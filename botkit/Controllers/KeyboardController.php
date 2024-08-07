<?php
// Контроллер клавиатур

namespace BotKit\Controllers;

use BotKit\Controller;
use BotKit\Enums\State;
use BotKit\Models\Messages\TextMessage as M;
use BotKit\Models\Attachments\PhotoAttachment;
use BotKit\Models\Keyboards\ClearKeyboard;

use BotKit\Keyboards\YTThumbnailKeyboardInline;
use BotKit\Keyboards\YTThumbnailKeyboard;
use BotKit\Keyboards\TestKeyboard;
use BotKit\Keyboards\TestKeyboardInline;

class KeyboardController extends Controller {
    
    public function clearKeyboard() {
        $m = M::create("Очищено");
        $m->setKeyboard(new ClearKeyboard());
        $this->reply($m);
    }

    public function getTestKeyboard() {
        $m = M::create("Вот клавиатура");
        $m->setKeyboard(new TestKeyboard());
        $this->reply($m);
    }
    
    public function getTestKeyboardInline($yt_id) {
        $m = M::create("Вот клавиатура");
        $m->setKeyboard(new TestKeyboardInline($yt_id));
        $this->reply($m);
    }
    
    public function getYTThumbnailLink($yt_id) {
        $m1 = M::create("regular");
        $m1->setKeyboard(new YTThumbnailKeyboard($yt_id));
        
        $m2 = M::create("inline");
        $m2->setKeyboard(new YTThumbnailKeyboardInline($yt_id));
        
        $this->reply($m1);
        $this->reply($m2);
    }
}
