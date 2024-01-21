<?php
// Файл обновления базы данных

namespace BotKit\Tools;

require_once __DIR__.'/../src/bootstrap.php';

DatabaseStarter::start();
EnumExporter::export();