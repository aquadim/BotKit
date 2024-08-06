<?php
// Контроллер клавиатур

namespace BotKit\Controllers;

use BotKit\Controller;
use BotKit\Enums\State;
use BotKit\Models\Messages\TextMessage as M;
use BotKit\Models\Attachments\PhotoAttachment;

use BotKit\Keyboards\TestKeyboard;

class KeyboardController extends Controller {

    public function getTestKeyboard() {
        $message = M::create("Вот клавиатура");
        $message->setKeyboard(new TestKeyboard());
        $this->reply($message);
    }
}