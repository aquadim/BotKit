<?php
// Драйвер бота для vk.com

namespace BotKit\Drivers;

use BotKit\Models\User as UserModel;
use BotKit\Models\Chats\IChat;
use BotKit\Models\Chats\DirectChat;
use BotKit\Models\Chats\GroupChat;
use BotKit\Models\Messages\TextMessage;
use BotKit\Models\Events\IEvent;
use BotKit\Models\Events\UnknownEvent;
use BotKit\Models\Events\TextMessageEvent;
use BotKit\Database;
use BotKit\Enums\State;
use BotKit\Models\Messages\IMessage;
use BotKit\Enums\PhotoAttachmentType;

class VkComDriver implements IDriver {

    // Домен
    private static string $domain = "vk.com";
    
    // API версия
    private string $api_version = "5.199";
    
    // Это запрос подтверждения сервера?
    private bool $request_is_confirmation;

    // JSON данные полученного POST запроса
    private array $post_body;
    
    // URL загрузки изображений
    protected string $uploadurl_photo;
    
    // Выполняет метод API
    public function execApiMethod(string $method, array $fields) : array {
        $fields["v"] = $this->api_version;
        $fields["access_token"] = $_ENV["vkcom_apikey"];
        $post_fields = http_build_query($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vk.com/method/".$method);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($output, true);
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
                return $this->post_body["object"]["message"]["from_id"];
            
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
    
    public function getUserName($id = null) : string {
        $user = $this->execApiMethod(
            "users.get",
            ["user_ids" => $id ? $id : $this->getUserIdOnPlatform()]
        )["response"][0];
        return $user["first_name"] . " " . $user["last_name"];
    }
    
    public function getNickName($id = null) : string {
        $user = $this->execApiMethod("users.get",
        [
            "user_ids" => $id ? $id : $this->getUserIdOnPlatform(),
            "fields" => "domain"
        ])["response"][0];
        return $user["domain"];
    }

    public function getEvent(UserModel $user_model) : IEvent {
        $type = $this->post_body["type"];
        if ($type == "confirmation") {
            exit($_ENV["vkcom_confirmation"]);
        }
        
        $object = $this->post_body["object"];
        $chat_with_user = new DirectChat($this->getUserIdOnPlatform());
        
        if ($object["message"]["peer_id"] > 2000000000) {
            $chat_of_msg = new GroupChat($object["message"]["peer_id"]);
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
                    $object["message"]["id"],
                    $user_model,
                    $chat_of_msg,
                    $object["message"]["text"],
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
        $this->execApiMethod("messages.send",
        [
            "user_id" => $user->getIdOnPlatform(),
            "random_id" => 0,
            "message" => $msg->getText()
        ]);
    }
    
    public function editMessage(IMessage $old, IMessage $new) : void {
        $attachment_strings = $this->getAttachmentStrings($new->getPhotos());
        
        // Выполнение метода API
        $this->execApiMethod("messages.edit",
        [
            "peer_id" => $old->getChat()->getIdOnPlatform(),
            "random_id" => 0,
            "message" => $new->getText(),
            "attachment" => implode(",", $attachment_strings),
            "message_id" => $old->getId()
        ]);
        
        // Присваиваем новому сообщению старый ID
        $new->setId($old->getId());
    }

    public function sendToChat(IChat $chat, IMessage $msg) : void {
        $attachment_strings = $this->getAttachmentStrings($msg->getPhotos());
        
        // Выполнение метода отправки
        $response = $this->execApiMethod("messages.send",
        [
            "peer_id" => $chat->getIdOnPlatform(),
            "random_id" => 0,
            "message" => $msg->getText(),
            "attachment" => implode(",", $attachment_strings)
        ]);
        $msg->setId(strval($response["response"]));
        $msg->setChat($chat);
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
        $this->execApiMethod("messages.send",
        [
            "peer_id" => $this->getUserIdOnPlatform(),
            "random_id" => 0,
            "message" => "Contents for " . $label . ":\n".$info
        ]);
    }

    public function getPlatformDomain() : string {
        return self::$domain;
    }
    #endregion
    
    protected function getUploadURLPhoto() : string {
        if (isset($this->uploadurl_photo)) {
            return $this->uploadurl_photo;
        }
        
        $response = $this->execApiMethod("photos.getMessagesUploadServer",
        [
            "public_id" => $_ENV["vkcom_public_id"]
        ]);
        $this->uploadurl_photo = $response["response"]["upload_url"];
        return $this->uploadurl_photo;
    }
    
    // Загружает изображение с диска. Возвращает строку, которую можно
    // использовать как $attachment
    protected function uploadImage($filename) : string {
        // Получение URL для загрузки фото
        $upload_url = $this->getUploadURLPhoto();
        
        $image = new \CURLFile($filename, 'image/jpeg');
        
        // Передача файла
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $upload_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file1' => $image]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response_afterupload = curl_exec($ch);
    
        $data_afterupload = json_decode($response_afterupload, true);
        
        $response = $this->execApiMethod("photos.saveMessagesPhoto",
        [
            'photo'=>$data_afterupload['photo'],
            'server'=>$data_afterupload['server'],
            'hash'=>$data_afterupload['hash'],
        ]);
        
        return 
            "photo".
            $response['response'][0]['owner_id'].
            '_'.
            $response['response'][0]['id'];
    }
    
    protected function getAttachmentStrings($photos) : array {
        $attachment_strings = [];
        
        // photo
        foreach ($photos as $photo) {
            switch ($photo->getType()) {
                case PhotoAttachmentType::FromFile:
                    $attachment = $this->uploadImage($photo->getValue());
                    $attachment_strings[] = $attachment;
                    $photo->setId($attachment);
                    break;
                
                case PhotoAttachmentType::FromURL:
                    break;
                
                case PhotoAttachmentType::FromUploaded:
                    $attachment_strings[] = $photo->getValue();
                    break;
                
                default:
                    break;
            }
        }
        
        return $attachment_strings;
    }
}
