<?php
// Файл, на который поступают запросы

namespace BotKit;

require_once "vendor/autoload.php";

use BotKit\Drivers\Driver;
use BotKit\Drivers\TgBotDriver;
use BotKit\Enums\FsmState;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

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
// TODO: реализовать метод onCommandWithParams который схватывает текст и
// передаёт его как параметр в callback
$bot->on(Driver::MSG_PLAIN, [Commands::class, 'gravatar'], function ($u, $e, $b) {
	return str_contains($e->getText(), "/gravatar");
});

// Ни одно условие не совпало
$bot->fallback([Commands::class, 'commandNotFound']);

$bot->handle();