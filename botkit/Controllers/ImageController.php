<?php
// Контроллер изображений

namespace BotKit\Controllers;

use BotKit\Controller;
use BotKit\Enums\State;
use BotKit\Models\Messages\TextMessage as M;
use BotKit\Models\Attachments\PhotoAttachment;

class ImageController extends Controller {

    public function thispersondoesnotexist() {
        // Отправка сообщения просьбы ожидания
        $wait_message = M::create("Изображение скачивается...");
        $this->reply($wait_message);
        
        // Скачиваем изображение, создаём временный файл и помещаем
        // в него данные изображения.
        $image_data = file_get_contents(
            "https://thispersondoesnotexist.com/"
        );
        $filename = tempnam("/tmp", "botkit").'.jpeg';
        file_put_contents($filename, $image_data);
        
        // Создание финального сообщения
        $photo = PhotoAttachment::fromFile($filename);
        $message = M::create("Фото с сайта https://thispesondoesnotexist.com");
        $message->addPhoto($photo);
        
        // Финальная отправка
        $this->edit($wait_message, $message);
    }
}