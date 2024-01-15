<?php
// Команды бота

namespace BotKit;

use BotKit\User;
use BotKit\EventData;
use BotKit\Drivers\Driver;
use BotKit\Keyboards;

class Commands {

	// Говорит "Привет, мир!"
	public static function sayHelloWorld(User $user, EventData $e, Driver $drv) {
		$drv->sendMessage($user, new Message("Привет, мир!"));
	}

	// Подтверждение сервера
	public static function displayHelp(User $user, EventData $e, Driver $drv) {
		$drv->sendMessage($user, Message::create("Help on this bot:\n"));
	}

	// Команда не найдена
	public static function commandNotFound(User $user, EventData $e, Driver $drv) {
		$drv->sendMessage($user, Message::create("I don't know how to handle this command"));
	}

	// Присылает меню
	public static function menu(User $user, EventData $e, Driver $drv) {
		$layout = [
			["/schedule", "/grades", "/whatsnext"],
			["/help"]
		];
		$drv->sendMessage(
			$user,
			Message::create("Главное меню")->withKeyboard(new Keyboards\MenuKeyboard($layout))
		);
	}
}