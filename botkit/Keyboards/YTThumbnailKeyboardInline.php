<?php

namespace BotKit\Keyboards;

use BotKit\Models\Keyboards\InlineKeyboard;
use BotKit\Models\KeyboardButtons\UrlKeyboardButton;
use BotKit\Enums\ButtonColor;

class YTThumbnailKeyboardInline extends InlineKeyboard {
    
    protected bool $cacheable = false;
    
    protected bool $one_time = false;
    
    public function __construct($yt_id) {
        $this->layout = [];
        for ($i = 1; $i < 4; $i++) {
            $btn = new UrlKeyboardButton(
                "Кадр #$i",
                ButtonColor::Primary,
                "https://img.youtube.com/vi/".$yt_id."/mq".$i.".jpg"
            );
            $this->layout[] = [$btn];
        }
    }
}