<?php
// Клавиатура главного меню

namespace BotKit\Keyboards;

class MenuKeyboard extends Keyboard {
	#region Драйвер-зависимые свойства
	public $tg_resize=true;
	public $tg_onetime=false;
	public $tg_specific=true;
	#endregion

	public static bool $cacheable = true;

	public function __construct(array $layout) {
		$this->layout = $layout;
	}

	public function getLayout() : array {
		return $this->layout;
	}
}