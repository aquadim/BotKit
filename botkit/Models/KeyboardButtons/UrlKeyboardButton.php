<?php
// Кнопка, которая содержит ссылку

namespace BotKit\Models\KeyboardButtons;
use BotKit\Enums\ButtonColor;

class UrlKeyboardButton implements IKeyboardButton {
    
    protected string $text;
    
    protected string $url;
    
    protected ButtonColor $color;
    
    public function setText(string $text) : void {
        $this->text = $text;
    }
    
    public function getText() : string {
        return $this->text;
    }

    public function setValue(string $value) : void {
        $this->url = $value;
    }
    
    public function getValue() : string {
        return $this->url;
    }
    
    public function setColor(ButtonColor $color) : void {
        $this->color = $color;
    }
    
    public function getColor() : ButtonColor {
        return $this->color;
    }
}
