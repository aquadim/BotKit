<?php
// Класс работы с БД
// Этот файл может быть изменён разработчиком

namespace BotKit\Common;

class Database {
	private static $db;
	private $connection;
	
	private function __construct($dsn, $user, $password) {
		// https://mariadb.com/resources/blog/developer-quickstart-php-data-objects-and-mariadb/
		$options = [
			\PDO::ATTR_EMULATE_PREPARES   => false,
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		];
		$this->connection = new \PDO($dsn, $user, $password);
	}

	public static function setCustomDsnValues($host, $db_name, $user, $password) : void {
		if (self::$db == null) {
			self::$db = new Database(
				"mysql:host=".$host.";dbname=".$db_name.";charset=utf8mb4",
				$user,
				$password
			);
		}
	}

	public static function getConnection() {
		if (self::$db == null) {
			self::$db = new Database(
				"mysql:host=".$_ENV['db_host'].";dbname=".$_ENV['db_name'].";charset=utf8mb4",
				$_ENV['db_user'],
				$_ENV['db_password']
			);
		}
		return self::$db->connection;
	}
}
