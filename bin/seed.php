#!/usr/bin/env php
<?php

require realpath(__DIR__ . '/../botkit/bootstrap.php');

use BotKit\Database;
use BotKit\Entities;

$em = Database::getEM();

$test_platform = new Entities\Platform("example.com");
$em->persist($test_platform);

$em->flush();
echo "Старт базы данных проведён успешно!";