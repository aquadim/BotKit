<?php
// Драйвер бота для тестирования

namespace BotKit\Drivers;

use BotKit\Models\User;
use BotKit\Models\Chats\Chat;
use BotKit\Models\Messages\TextMessage;
use BotKit\Models\Events\Event;
use BotKit\Models\Events\UnknownEvent;
use BotKit\Models\Events\TextMessageEvent;
use BotKit\Database;

class TestingDriver implements IDriver {

    // Буфер действий
    protected array $actions = [];

    public function forThis() : bool {
        // Проверить заголовок запроса TESTINGDRIVER
        if (isset($_SERVER['HTTP_TESTINGDRIVER'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getEvent() : Event {
        $data       = json_decode(file_get_contents("php://input"), true);
        $type       = $data['type'];
        $details    = $data['details'];
        $chat       = new Chat(42);

        switch ($type) {
            default:
                $user = $this->getUser($details['userId']);
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

    public function getUser() : User {
        $em = Database::getEM();

        // Получение объекта сущности

        // Возврат объекта пользователя
    }

    // Отвечает на текстовое сообщение
    // Если empathise=true, сообщение должно быть явным ответом
    // $e - событие, на которое создаётся ответ
    // $msg - сообщение ответа
    public function reply(
        TextMessageEvent $e,
        Message $msg,
        bool $empathise = true) : void;

    // Отсылает сообщение пользователю в личный чат между ботом и пользователем
    public function sendMessage(User $u, Message $msg) : void;

    // Изменяет сообщение
    // message_id - ID сообщения на платформе
    // msg - новые данные сообщения
    public function editMessage($message_id, Message $msg) : void;

    // Отправляет сообщение в чат
    public function sendToChat(Chat $chat, Message $msg);

    // Возвращает ник пользователя, например @aquadim
    public function getUserNick(User $u) : string;

    // Событие перед началом обработки запроса
    public function onProcessStart() : void;

    // Событие после ensureDriversLoaded
    public function onSelected() : void;

    // Событие завершения обработки
    public function onProcessEnd() : void;

    // Событие сохранения пользователя в БД
    public function onUserSave(User $user) : void;

    // Показывает содержимое переменной (для отладки)
    // $label - что именно за переменная
    // $variable - значение переменной
    public function showContent(string $label, $variable) : void;

    public function getPlatformDomain() : string {
        return 'example.com';
    }
}