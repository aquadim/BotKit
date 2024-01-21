<?php
// Класс для экспорта перечислений в БД

namespace BotKit\Tools;

require_once __DIR__.'/../src/bootstrap.php';

use BotKit\Common\Database;
use BotKit\Enums\FsmState;
use BotKit\Enums\Platform;

class EnumExporter {
	public static function export() : void {
		$db = Database::getConnection();

		// Экспорт состояний
		self::executeExport($db, 'bk_state', FsmState::cases());
		
		// Экспорт платформ
		self::executeExport($db, 'bk_platform', Platform::cases());
	}

	// Выполняет экспорт в конкретную таблицу
	private static function executeExport($db, $table_name, $cases) : void {
		$stm = $db->prepare(
			"INSERT INTO ".$table_name." (enum_id,name) VALUES(:enum_id,:name)"
		);
		$stm->bindParam(':enum_id', $enum_id, \PDO::PARAM_INT);
		$stm->bindParam(':name', $name, \PDO::PARAM_STR);

		foreach ($cases as $directive) {
			$enum_id = $directive->value;
			$name = $directive->name;
			$stm->execute();
		}
	}
}