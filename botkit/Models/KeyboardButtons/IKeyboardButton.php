<?php
// Интерфейс кнопки клавиатуры

namespace BotKit\Models\KeyboardButtons;
use BotKit\Enums\ButtonColor;

interface IKeyboardButton {
    
    // Устанавливает текст, отображаемый на кнопке
    public function setText(string $text) : void;
    
    // Возвращает текст, отображаемый на кнопке
    public function getText() : string;

    // Устанавливает доп. значение кнопки в формате ключ-значение
    public function setValue(array $value) : void;
    
    // Возвращает доп. значение кнопки
    public function getValue() : array;
    
    // Устанавливает цвет
    public function setColor(ButtonColor $color) : void;
    
    // Возвращает цвет
    public function getColor() : ButtonColor;
}

