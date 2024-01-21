<?php
// Файл, на который поступают запросы

namespace BotKit;

require_once __DIR__.'/src/bootstrap.php';

use BotKit\Drivers\Driver;
use BotKit\Drivers\TgBotDriver;
use BotKit\Enums\FsmState;
use BotKit\Common\Commands;
use BotKit\Common\Bot;

$tgdriver = new TgBotDriver($_ENV['tg_token']);

$bot = new Bot();
$bot->loadDriver($tgdriver);

// Отправка меню
$bot->onCommand('/menu', [Commands::class, 'menu']);

// Команда /help
$bot->onCommand('/help', [Commands::class, 'displayHelp']);

// Отправка котёнка
$bot->onCommand('/picture', [Commands::class, 'kitty']);

// Отправка граватара
$bot->onCommandWithParams('/gravatar {email} {size}', [Commands::class, 'gravatar']);
$bot->onCommandWithParams('/gravatar {email}', [Commands::class, 'gravatar']);
$bot->on(Driver::MSG_PLAIN, [Commands::class, 'gravatarHelp'], function ($u, $e) {
	return preg_match('/^\/gravatar/', $e->getText());
});

// Ни одно условие не совпало
$bot->fallback([Commands::class, 'commandNotFound']);

$bot->handle();