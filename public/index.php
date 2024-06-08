<?php
// Файл на который поступают запросы

require '../bot/bootstrap.php';

// Загрузка драйверов
use BotKit\Drivers\TestingDriver;

Bot::loadDriver(new TestingDriver());

Bot::ensureDriversLoaded();

require root_dir . '/src/handle.php';