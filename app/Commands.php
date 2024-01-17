<?php
// Команды бота

namespace BotKit;

use BotKit\User;
use BotKit\EventData;
use BotKit\Drivers\Driver;
use BotKit\Keyboards;
use BotKit\Attachments\ImageAttachment;

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
		$drv->sendMessage(
			$user,
			Message::create("Главное меню")->withKeyboard(new Keyboards\MenuKeyboard())
		);
	}

	public static function kitty(User $user, EventData $e, Driver $drv) {
		$msg = Message::create()->withImage(ImageAttachment::fromFile("/home/sysadmin/kitty.jpg"));
		$drv->sendMessage($user, $msg);
	}

	// Отправка изображения gravatar по email
	public static function gravatar(User $user, EventData $e, Driver $drv, $email, $size=256) {
		$hash = hash('sha256', $email);
		$url = 'https://www.gravatar.com/avatar/'.$hash."?s=".$size;

		$msg = Message::create("Граватар почты $email:")->withImage(ImageAttachment::fromUrl($url));
		$drv->sendMessage($user, $msg);
	}

	public static function gravatarHelp(User $user, EventData $e, Driver $drv) {
		$msg = Message::create("Справка по команде /gravatar:\nЭта команда присылает граватар человека\n/gravatar <почта> [размер изображения]");
		$drv->sendMessage($user, $msg);
	}
}