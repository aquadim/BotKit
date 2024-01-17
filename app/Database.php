<?php
// Класс работы с БД
// Этот файл может быть изменён разработчиком

namespace BotKit;

class Database {
	private static $db;
	private $connection;
	
	private function __construct() {
		// https://mariadb.com/resources/blog/developer-quickstart-php-data-objects-and-mariadb/
		$dsn = "mysql:host=".$_ENV['db_host']";dbname=".$_ENV['db_name'].";charset=utf8mb4";
		$options = [
			\PDO::ATTR_EMULATE_PREPARES   => false,
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		];
		$this->connection = new \PDO($dsn, $_ENV['db_user'], $_ENV['db_password']);
	}

	function __destruct() {
		$this->connection->close();
	}

	public static function getConnection() {
		if (self::$db == null) {
			self::$db = new Database();
		}
		return self::$db->connection;
	}
}