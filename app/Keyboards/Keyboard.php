<?php
// Приложение к сообщению: клавиатура
// Родительский класс - только для наследования

namespace BotKit\Keyboards;

abstract class Keyboard {
	// Можно ли кэшировать эту клавиатуру
	public static bool $cacheable = false;

	// Расположение кнопок в клавиатуре
	private array $layout;
}