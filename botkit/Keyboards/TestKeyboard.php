<?php

namespace BotKit\Keyboards;

use BotKit\Models\Keyboards\TextKeyboard;
use BotKit\Models\KeyboardButtons\TextKeyboardButton;
use BotKit\Enums\ButtonColor;

class TestKeyboard extends TextKeyboard {
    
    protected bool $cacheable = true;
    
    protected bool $one_time = false;
    
    public function __construct() {
        $this->layout = [
            [
                new TextKeyboardButton("A"),
                new TextKeyboardButton("B")
            ],
            [
                new TextKeyboardButton("C"),
                new TextKeyboardButton("D")
            ]
        ];
    }
}