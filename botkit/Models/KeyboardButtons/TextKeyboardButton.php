<?php
// Кнопка с текстом
// При нажатии на кнопку должно отправиться сообщение от имени
// пользователя с текстом, таким же как на кнопке
// Так же могут отправиться данные из $payload

namespace BotKit\Models\KeyboardButtons;
use BotKit\Enums\ButtonColor;

class TextKeyboardButton implements IKeyboardButton {
    
    protected string $text;
    
    protected array $payload;
    
    protected ButtonColor $color;
    
    public function __construct(
        string $text,
        ButtonColor $color = ButtonColor::Primary,
        ?array $payload = null
    )
    {
        $this->text = $text;
        $this->color = $color;
        if ($payload == null) {
            $this->payload = [];
        } else {
            $this->payload = $payload;
        }
    }
    
    public function setText(string $text) : void {
        $this->text = $text;
    }
    
    // Возвращает текст, отображаемый на кнопке
    public function getText() : string {
        return $this->text;
    }

    // Устанавливает значение клавиатуры
    public function setValue(array $value) : void {
        $this->payload = $value;
    }
    
    // Возвращает значение клавиатуры
    public function getValue() : array {
        return $this->payload;
    }
    
    // Устанавливает цвет
    public function setColor(ButtonColor $color) : void {
        $this->color = $color;
    }
    
    // Возвращает цвет
    public function getColor() : ButtonColor {
        return $this->color;
    }
}
