<?php
// Файл, на который поступают запросы

namespace BotKit;

require_once "vendor/autoload.php";

use BotKit\Drivers\Driver;
use BotKit\Drivers\TgBotDriver;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$tgdriver = new TgBotDriver($_ENV['tg_token']);

$bot = new Bot();
$bot->loadDriver($tgdriver);

// Первое взаимодействие
$bot->on(Driver::MSG_PLAIN, [Commands::class, 'sayHelloWorld'], function($u, $e) {
	return ($u->getState() == FsmState::HelloWorld && $e->textIs('Привет'));
});

$bot->onCommand('/menu', [Commands::class, 'menu']);

// Команда /help
$bot->onCommand('/help', [Commands::class, 'displayHelp']);

// Ни одно условие не совпало
$bot->fallback([Commands::class, 'commandNotFound']);

$bot->handle();