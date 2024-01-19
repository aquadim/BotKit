<?php
// Класс работы с БД
// Этот файл может быть изменён разработчиком

namespace BotKit\Common;

class Database {
	private static $db;
	private $connection;
	
	private function __construct($dsn) {
		// https://mariadb.com/resources/blog/developer-quickstart-php-data-objects-and-mariadb/
		echo "Accepted dsn: $dsn";
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

	public static function setCustomDsnValues($host, $db_name) : void {
		if (self::$db == null) {
			self::$db = new Database("mysql:host=".$host.";dbname=".$db_name.";charset=utf8mb4");
		}
	}

	public static function getConnection() {
		if (self::$db == null) {
			self::$db = new Database("mysql:host=".$_ENV['db_host'].";dbname=".$_ENV['db_name'].";charset=utf8mb4");
		}
		return self::$db->connection;
	}
}
