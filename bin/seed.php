#!/usr/bin/env php
<?php

require realpath(__DIR__ . '/../botkit/bootstrap.php');

use BotKit\Database;
use BotKit\Entities;

$em = Database::getEM();

$vk_platform = new Entities\Platform("vk.com");
$em->persist($vk_platform);

$em->flush();
echo "Старт базы данных проведён успешно!";
