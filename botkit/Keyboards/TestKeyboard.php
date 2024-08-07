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
                new TextKeyboardButton("A", ButtonColor::Primary),
                new TextKeyboardButton("B", ButtonColor::Secondary)
            ],
            [
                new TextKeyboardButton("C", ButtonColor::Negative),
                new TextKeyboardButton("D", ButtonColor::Positive)
            ]
        ];
    }
}