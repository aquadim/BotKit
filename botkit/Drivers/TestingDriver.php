<?php
// Драйвер бота для тестирования

namespace BotKit\Drivers;

use BotKit\Models\User as UserModel;
use BotKit\Models\Chats\Chat;
use BotKit\Models\Messages\TextMessage;
use BotKit\Models\Events\Event;
use BotKit\Models\Events\UnknownEvent;
use BotKit\Models\Events\TextMessageEvent;
use BotKit\Database;
use BotKit\Entities\{User as UserEntity, Platform};

class TestingDriver implements IDriver {

    // Буфер действий
    protected array $actions = [];

    // Домен
    private static string $domain = "example.com";

    // JSON данные полученного POST запроса
    private static string $post_body;

    #region IDriver
    public function forThis() : bool {
        // Проверить заголовок запроса TESTINGDRIVER
        if (isset($_SERVER['HTTP_TESTINGDRIVER'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getEvent() : Event {
        $this->post_body = json_decode(
            file_get_contents("php://input"),
            true);
        $type       = $this->post_body['type'];
        $details    = $this->post_body['details'];
        $chat       = new Chat(42);

        switch ($type) {
            default:
                $user = $this->getUserModel($details['userId']);
                return new UnknownEvent($user);
        }

        // Интерфейс тестов запрашивает установку состояния
        //~ if ($data['type'] == 'stateSet') {
            //~ $user = $this->getUser($details['userID']);
            //~ $user->setState(State::from($details['stateID']));

            //~ // Сохраняем пользователя вручную
            //~ UserModel::updateObject($user->getDbObject());
            
            //~ $this->actions[] = [
                //~ "action" => "info",
                //~ "title" => "Состояние пользователя изменено вручную",
                //~ "body" => "Новое состояние: ".serialize($user->getState())
            //~ ];
            //~ // Дальнейшая обработка не требуется, завершаем выполнение здесь
            //~ $this->echoActions();
        //~ }

        //~ // Интерфейс тестов запрашивает все доступные состояния
        //~ if ($data['type'] == 'statesRequest') {
            //~ $this->actions[] = [
                //~ "action" => "statesResponse",
                //~ "states" => array_combine(
                    //~ array_column(State::cases(), 'name'),
                    //~ array_column(State::cases(), 'value')
                //~ )
            //~ ];
            //~ // Дальнейшая обработка не требуется, завершаем выполнение здесь
            //~ $this->echoActions();
        //~ }

        //~ if ($data['type'] == 'callback') {
            //~ // Обратный вызов
            //~ $user = $this->getUser($details['userId']);
            //~ return new CallbackEvent(
                //~ $details['msgId'],
                //~ $user,
                //~ $chat,
                //~ CallbackType::from($details['callbackType']),
                //~ $details['params']
            //~ );
        //~ }

        //~ if ($data['type'] == 'botKitMsg') {
            //~ // Обычное текстовое сообщение
            //~ $user = $this->getUser($details['userID']);
            //~ $text = $details['text'];
            //~ return new PlainMessageEvent(
                //~ $details['id'],
                //~ $user,
                //~ $chat,
                //~ $text,
                //~ []
            //~ );
        //~ }
    }

    public function getUserModel(string $id_on_platform) : UserModel {
        // Получение объекта сущности
        $em = Database::getEM();
        $query = $em->createQuery(
            'SELECT user, platform FROM '.UserEntity::class.' user '.
            'JOIN user.platform platform '.
            'WHERE platform.domain="'.self::$domain.'" '.
            'AND user.id_on_platform=:id_on_platform');
        $query->setParameters(['id_on_platform'=>$id_on_platform]);
        $user_entity = $query->getResult();

        // Возврат объекта пользователя
        return new UserModel(
            $user_entity,
            $this->post_body['details']['userName']);
    }
    
    public function reply(
        TextMessageEvent $e,
        IMessage $msg,
        bool $empathise = true) : void
    {
        if ($empathise) {
            $reply_to_id = $e->getMessageID();
        } else {
            $reply_to_id = -1;
        }
        $this->sendInternal($msg, $reply_to_id);
    }

    public function sendMessage(User $u, IMessage $msg) : void {
        $this->sendInternal($msg, -1);
    }
    
    public function editMessage($message_id, IMessage $msg) : void {
        $this->addAction(
            'editMessage',
            [
                'msgId'=>$message_id,
                'newMessage'=>$this->getMessageData($msg, -1)
            ]);
    }

    public function sendToChat(Chat $chat, IMessage $msg) : void {
        $this->sendInternal($msg, -1);
    }
    
    public function onSelected() : void {
        set_error_handler([$this, "errorHandler"], E_ALL);
        set_exception_handler([$this, "exceptionHandler"]);
    }

    public function onProcessStart(UserModel $user) : void {
        $this->start_user_state = $user->getState();
    }

    // Событие завершения обработки
    public function onProcessEnd(UserModel $user) : void {
        $end_state = $user->getState();
        if ($this->start_user_state != $end_state) {
            // Если вначале пользователь был в одном состоянии, а в конце
            // в другом, уведомляем об этом
            $this->addAction('info',
                [
                    "title" => "Состояние пользователя изменено",
                    "body" => "Новое состояние: ".serialize($end_state)
                ]
            );
        }
        $this->echoActions();
    }

    // Показывает содержимое переменной (для отладки)
    // $label - что именно за переменная
    // $variable - значение переменной
    public function showContent(string $label, $variable) : void {
        ob_start();
        var_dump($variable);
        $info = ob_get_clean();
        $this->addAction("varDump",
            [
                "title" => $title,
                "info" => $info 
            ]
        );
    }

    public function getPlatformDomain() : string {
        return 'example.com';
    }
    #endregion

    // Добавляет действие в буфер
    protected function addAction(string $command, array $details) : void {
        $details['forUser'] = $this->post_body['details']['userId'];
        $this->actions[] = ['action' => $command, 'details' => $details];
    }

    // Отправляет сообщение
    protected function sendInternal(IMessage $msg, int $reply_to_id) : void {
        $this->addAction(
            'newMessage',
            $this->getMessageData($msg, $reply_to_id));
    }

    // Возвращает разметку для сообщения
    private function getMessageData(IMessage $msg, int $reply_to_id) : array {
        //~ $attachments = [];

        //~ // Поиск клавиатур
        //~ if ($msg->hasKeyboard()) {
            //~ $keyboard = $msg->getKeyboard();

            //~ // Определение типа
            //~ if ($keyboard->inline) {
                //~ $attachment_type = "inlineKeyboard";
            //~ } else {
                //~ $attachment_type = "keyboard";
            //~ }

            //~ $serialized_layout = [];

            //~ // Разметка
            //~ $layout = $keyboard->getLayout();
            //~ foreach ($layout as $row) {
                //~ $serialized_row = [];
                //~ foreach ($row as $button) {

                    //~ // Определение типа
                    //~ if (is_a($button, CallbackButton::class)) {
                        //~ // Кнопка обратного вызова
                        //~ $button_type = "callbackButton";
                    //~ } else {
                        //~ $button_type = "button";
                    //~ }

                    //~ // Определение цвета
                    //~ switch ($button->getColor()) {
                        //~ case KeyboardButtonColor::Primary:
                            //~ $button_color = "primary";
                            //~ break;
                        //~ case KeyboardButtonColor::Secondary:
                            //~ $button_color = "secondary";
                            //~ break;
                        //~ default:
                            //~ $button_color = "primary";
                            //~ break;
                    //~ }

                    //~ $button_data = [
                        //~ "type" => $button_type,
                        //~ "color" => $button_color,
                        //~ "label" => $button->getText()
                    //~ ];

                    //~ // Добавление параметров обратного вызова
                    //~ if ($button_type == "callbackButton") {
                        //~ $button_data["callbackType"] = $button->getType();
                        //~ $button_data["payload"] = $button->getPayload();
                    //~ }

                    //~ $serialized_row[] = $button_data;
                //~ }
                //~ $serialized_layout[] = $serialized_row;
            //~ }

            //~ $attachments[] = [
                //~ "type" => $attachment_type,
                //~ "layout" => $serialized_layout
            //~ ];
        //~ }

        //~ // Поиск изображений
        //~ if ($msg->hasImages()) {
            //~ $images = $msg->getImages();
            //~ foreach ($images as $image) {
                //~ $attachments[] = [
                    //~ 'type' => 'image',
                    //~ 'url' => $image->getValue()
                //~ ];
            //~ }
        //~ }

        return [
            "text" => $msg->getText(),
            "reply_to" => $reply_to_id
        ];
    }

    public function errorHandler(
        int $errno,
        string $errstr,
        string $errfile = null,
        int $errline = null,
        array $errcontext = null
    ): bool {

        $meaning = [
            E_ERROR => "error",
            E_WARNING => "warning",
            E_PARSE => "error",
            E_NOTICE => "warning",
            E_CORE_ERROR => "error",
            E_CORE_WARNING => "warning",
            E_COMPILE_ERROR => "error",
            E_COMPILE_WARNING => "warning",
            E_USER_ERROR => "error",
            E_USER_WARNING => "warning",
            E_USER_NOTICE => "warning",
            E_STRICT => "warning",
            E_RECOVERABLE_ERROR => "error",
            E_DEPRECATED => "warning",
            E_USER_DEPRECATED => "warning"
        ];

        $this->addAction(
            $meaning[$errno]."Message",
            [
                "line" => $errline,
                "file" => $errfile,
                "trace" => "<Нет стека вызовов>",
                "msg" => $errstr
            ]
        );

        if ($meaning[$errno] === 'error') {
            // Если произошла фатальная ошибка, завершаем работу
            $this->echoActions();
            return true; // Не достигается
        } else {
            return false;
        }
    }

    public function exceptionHandler($ex) : void {
        $this->addAction(
            "errorMessage",
            [
                "line" => $ex->getLine(),
                "file" => $ex->getFile(),
                "trace" => $ex->getTraceAsString(),
                "msg" => $ex->getMessage()
            ]
        );
        $this->echoActions();
    }

    // Выводит все события в JSON
    protected function echoActions() {
        echo json_encode($this->actions);
        exit();
    }
}