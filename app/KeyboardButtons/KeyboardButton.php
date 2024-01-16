<?php
// Кнопка клавиатуры
// Родительский класс - только для наследования

namespace BotKit\KeyboardButtons;

use BotKit\Enums\KeyboardButtonColor;

abstract class KeyboardButton {
	// Текст кнопки
	protected string $text;

	// Цвет кнопки
	protected KeyboardButtonColor $color;

	// Дополнительная драйвер-специфичная информация
	protected array $payload;

	public function getText() {
		return $this->text;
	}
}