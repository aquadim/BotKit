<?php

namespace BotKit\Keyboards;

use BotKit\Models\Keyboards\InlineKeyboard;
use BotKit\Models\KeyboardButtons\TextKeyboardButton;
use BotKit\Models\KeyboardButtons\CallbackButton;
use BotKit\Enums\ButtonColor;
use BotKit\Enums\CallbackType;

class TestKeyboardInline extends InlineKeyboard {
    
    protected bool $cacheable = true;
    protected bool $one_time = false;
    
    public function __construct($yt_id) {
        $this->layout = [
            [
                new CallbackButton(
                    "Напечатать Hello World",
                    CallbackType::HelloWorld,
                    [],
                    ButtonColor::Primary
                ),
                
                new CallbackButton(
                    "Превью от https://youtu.be/".$yt_id,
                    CallbackType::YoutubeShowPreview,
                    ['yt_id' => $yt_id],
                    ButtonColor::Primary,
                )
            ]
        ];
    }
}
