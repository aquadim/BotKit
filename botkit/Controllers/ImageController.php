<?php
// Контроллер изображений

namespace BotKit\Controllers;

use BotKit\Controller;
use BotKit\Enums\State;
use BotKit\Models\Messages\TextMessage as M;
use BotKit\Models\Attachments\PhotoAttachment;

class ImageController extends Controller {

    public function thispersondoesnotexist($count) {
        // Отправка сообщения просьбы ожидания
        $wait_message = M::create("Изображения скачиваются ($count)...");
        $this->reply($wait_message);
        
        $message = M::create("Фото с сайта https://thispesondoesnotexist.com");
        for ($i = 0; $i < $count; $i++) {
            $message->addPhoto(PhotoAttachment::fromURL($filename));
        }
        
        // Финальная отправка
        $this->edit($wait_message, $message);
    }
    
    public function gravatar($email, $size) {
        $wait_message = M::create("Изображение скачивается...");
        $this->reply($wait_message);
        
        $final = M::create("Gravatar: $email");
        $grav_url = 
            "https://www.gravatar.com/avatar/".
            hash("sha256",strtolower(trim($email))).
            "?s=".
            $size;
        $final->addPhoto(PhotoAttachment::fromURL($grav_url));
        
        $this->edit($wait_message, $final);
    }
    
    public function ytPreview($yt_id) {
        // wait section
        $wait_message = M::create("Изображения скачиваеюся...");
        $this->reply($wait_message);
        
        // general section
        $m = M::create("Кадры из видео: ");
        for ($i = 1; $i < 4; $i++) {
            $m->addPhoto(PhotoAttachment::fromURL(
                "https://img.youtube.com/vi/".$yt_id."/mq".$i.".jpg"
            ));
        }
        $this->reply($m);
    }
}