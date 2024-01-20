<?php
// Автоматически сгенерировано 

namespace BotKit\Models;

trait %s {
	protected static string $table_name = "%s";

	//==Пояснения колонок==//
%10$s
	public static function create(%s) {
		$db = Database::getConnection();
		$statement = $db->prepare(
			"INSERT INTO ".static::$table_name." (%s) VALUES (%s)"
		);
%s
		$statement->execute();
	}

	public static function updateObject($id,%s) {
		$db = Database::getConnection();
		$statement = $db->prepare(
			"UPDATE ".static::$table_name." SET %s WHERE id=:id"
		);
		$statement->bindValue(':id', $id);
%s
		$statement->execute();
	}
}

