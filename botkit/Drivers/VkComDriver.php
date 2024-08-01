<?php
// Драйвер бота для vk.com

namespace BotKit\Drivers;

use BotKit\Models\User as UserModel;
use BotKit\Models\Chats\IChat;
use BotKit\Models\Chats\DirectChat;
use BotKit\Models\Messages\TextMessage;
use BotKit\Models\Events\IEvent;
use BotKit\Models\Events\UnknownEvent;
use BotKit\Models\Events\TextMessageEvent;
use BotKit\Database;
use BotKit\Enums\State;

use BotKit\Models\Messages\IMessage;

class VkComDriver implements IDriver {

    // Домен
    private static string $domain = "vk.com";
    
    // Это запрос подтверждения сервера?
    private bool $request_is_confirmation;

    // JSON данные полученного POST запроса
    private array $post_body;
    
    // Выполняет метод API
    private function execApiMethod(string $method, array $fields) : array {
        $post_fields = http_build_query($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vk.com/method/".$method);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($output, true)["response"];
    }

    #region IDriver
    public function forThis() : bool {
        $this->post_body = json_decode(
            file_get_contents('php://input'),
            true
        );
        
        // В запросе от ВКонтакте должны быть эти поля:
        $required = ['type', 'group_id'];
        foreach ($required as $field) {
            if (!isset($this->post_body[$field])) {
                return false;
            }
        }
        return true;
    }

    public function getUserIdOnPlatform() : string {
        switch ($this->post_body["type"]) {
            case "message_new":
            case "message_edit":
            case "message_typing_state":
                return $this->post_body["object"]["from_id"];
            
            case "message_allow":
            case "message_deny":
            case "message_event":
                return $this->post_body["object"]["user_id"];
            
            case "confirmation":
            default:
                // Запрос отправляет не пользователь, а сам ВКонтакте
                // Говорим что это псевдо-пользователь с ID -1
                return -1;
        }
    }
    
    public function getUserName() : string {
        $user = $this->execApiMethod(
            "users.get",
            ["user_ids" => $this->getUserIdOnPlatform()]
        )[0];
        return $user["first_name"] . " " . $user["last_name"];
    }

    public function getEvent(UserModel $user_model) : IEvent {
        $type = $this->post_body["type"];
        if ($type == "confirmation") {
            exit($_ENV["vkcom_confirmation"]);
        }
        
        $object = $this->post_body["object"];
        $chat_with_user = new DirectChat($this->getUserIdOnPlatform());
        
        // Если существует в объекте chat_id, то сообщение написано в
        // групповом чате
        if (isset($object["chat_id"])) {
            $chat_of_msg = new GroupChat($object["chat_id"]);
        } else {
            $chat_of_msg = $chat_with_user;
        }
        
        // Закрываем соединение для того чтобы скрипт мог работать больше чем 10 секунд
		// Скрипт должен уметь работать больше чем 10 секунд потому что если vk не получил "ok"
		// за 10 секунд от сервера, он пришлёт запрос ещё раз. На самом деле сервер обрабатывал первый
		// запрос, и когда он его закончил, он ответил бы "ok", но второй запрос уже прислался...
		// Так будет происходить 5 раз перед тем как вк не сдастся и не прекратит присылать новые запросы
		// https://ru.stackoverflow.com/q/893864/418543
		ob_end_clean();
		header("Connection: close");
		ignore_user_abort(true);
		ob_start();
		echo "ok";
		$size = ob_get_length();
		header("Content-Length: ".$size);
		ob_end_flush();
		flush();
        
        switch ($type) {
            case "message_new":
                return new TextMessageEvent(
                    $object["id"],
                    $user_model,
                    $chat_of_msg,
                    $object["text"],
                    []
                );
            
            default:
                return new UnknownEvent($user_model, $chat_of_msg);
        }
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

    public function sendDirectMessage(UserModel $user, IMessage $msg) : void {
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

    public function sendToChat(IChat $chat, IMessage $msg) : void {
        $this->sendInternal($msg, -1);
    }
    
    public function onSelected() : void {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }

    public function onProcessStart() : void {}

    public function onProcessEnd() : void {}

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
        return 'vk.com';
    }
    #endregion
}
