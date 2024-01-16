<?php
// Клавиатура главного меню

namespace BotKit\Keyboards;

use BotKit\KeyboardButtons\PlainKeyboardButton;

class MenuKeyboard extends Keyboard {
	#region Драйвер-зависимые свойства
	public $tg_resize=true;
	public $tg_onetime=false;
	public $tg_specific=true;
	#endregion

	public function __construct() {
		
		$this->layout =
		[
			[	new PlainKeyboardButton("/schedule"),
				new PlainKeyboardButton("/grades"),
				new PlainKeyboardButton("/next")
			],
			[	new PlainKeyboardButton("/help")
			]
		];
	}

	public static bool $cacheable = true;
}