<?php
// Кнопка с дополнительными данными

namespace BotKit\Models\KeyboardButtons;
use BotKit\Enums\ButtonColor;

class PayloadKeyboardButton implements IKeyboardButton {
    
    protected string $text;
    
    protected string $payload;
    
    protected ButtonColor $color;
    
    public function setText(string $text) : void {
        $this->text = $text;
    }
    
    public function getText() : string {
        return $this->text;
    }

    public function setValue(string $value) : void {
        $this->payload = $value;
    }
    
    public function getValue() : string {
        return $this->payload;
    }
    
    public function setColor(ButtonColor $color) : void {
        $this->color = $color;
    }
    
    public function getColor() : ButtonColor {
        return $this->color;
    }
}
