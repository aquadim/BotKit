<?php
// Класс работы с моделями
// Только для наследования

namespace BotKit\Models;

use BotKit\Common\Database;

class Model {
	protected static $allowed_columns;

	private static function allowedColumnsSQL() {
		return implode(',', $this->allowed_columns);
	}

	// Возвращает все записи из таблицы
	public static function all() {
		$db = Database::getConnection();
		return $db->query("SELECT ".$this->allowedColumnsSQL()." FROM ".static::$table_name);
	}

	// Возвращает записи по условиям
	// Все условия должны быть истины, т. к. для построения запроса используется 'AND'
	public static function where($conditions) {
		// Построение текста условий
		$conditions_sql = '';
		foreach ($conditions as $condition) {
			$conditions_sql .= $condition[0].$condition[1].':'.$condition[0];
		}
		
		$sql = "SELECT ".$this->allowedColumnsSQL()." FROM ".static::$table_name." WHERE ".$conditions_sql;
	}

	public static function sayTableName() {
		echo static::$table_name;
	}
}