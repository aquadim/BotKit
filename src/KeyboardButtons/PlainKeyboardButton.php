<?php
// Кнопка клавиатуры, нажав на которую отправляется текстовое сообщение
// от имени пользователя

namespace BotKit\KeyboardButtons;

use BotKit\Enums\KeyboardButtonColor;

class PlainKeyboardButton extends KeyboardButton {
	public function __construct(string $text, KeyboardButtonColor $color=KeyboardButtonColor::None, $payload=[]) {
		$this->text = $text;
		$this->color = $color;
		$this->payload = $payload;
	}
}