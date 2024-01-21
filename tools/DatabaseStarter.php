<?php
// Скрипт для быстрого создания базы данных
// Пока что поддерживается только MySql/MariaDB

namespace BotKit\Tools;

require_once __DIR__.'/../src/bootstrap.php';

use BotKit\Common\Database;

class DatabaseStarter {
	public static function start() : void {
		$db = Database::getConnection();

		// Таблица платформ
		$db->query(
		"CREATE TABLE IF NOT EXISTS `bk_platform` (
		 `id` int(11) NOT NULL AUTO_INCREMENT,
		 `enum_id` int(11) NOT NULL COMMENT 'ID платформы из перечисления',
		 `name` text NOT NULL COMMENT 'Название платформы из перечисления',
		 PRIMARY KEY (`id`))"
		);

		// Таблица состояний пользователя
		$db->query(
		"CREATE TABLE IF NOT EXISTS `bk_state` (
		 `id` int(11) NOT NULL AUTO_INCREMENT,
		 `enum_id` int(11) NOT NULL COMMENT 'ID состояния из перечисления',
		 `name` text NOT NULL COMMENT 'Название состояния из перечисления',
		 PRIMARY KEY (`id`))"
		);

		// Таблица пользователей
		$db->query(
		"CREATE TABLE IF NOT EXISTS `bk_user` (
		 `id` int(11) NOT NULL AUTO_INCREMENT,
		 `id_on_platform` text NOT NULL COMMENT 'ID пользователя на платформе',
		 `fk_platform` int(11) NOT NULL COMMENT 'Платформа на которой пользователь взаимодействует с ботом',
		 `fk_state` int(11) NOT NULL COMMENT 'Состояние пользователя',
		 PRIMARY KEY (`id`))"
		);
	}
}
