<?php

// Общий файл инициализации

define('rootdir', __DIR__.'/../');

require_once rootdir."vendor/autoload.php";

$dotenv = \Dotenv\Dotenv::createImmutable(rootdir);
$dotenv->load();
