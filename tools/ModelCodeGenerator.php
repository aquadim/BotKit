<?php

/* Этот скрипт получает информацию из базы данных о таблицах и их колонках
и на основании этого создаёт файлы моделей. На самом деле создаются трейты.
В самом классе модели указывается, что модель наследуется от абстрактного
BotKit\Models\Model и используется трейт, который был сгенерирован этим
скриптом. Таким образом изменения, внесённые разработчиком не перезапишутся,
если схема БД обновится и потребуется обновление. */

namespace BotKit\Tools;

require_once __DIR__.'/../vendor/autoload.php';

use BotKit\Common\Database;

// Таблица БД
/* Таблицу следует создавать в единственном числе, т.к. класс создастся с
названием как раз имени. (Например: Для таблицы 'user' будет создан класс
'User') */
class Table {
	private string $name;
	private array $columns;

	public function __construct(string $name, array $columns) {
		$this->name = $name;
		$this->columns = $columns;
	}

	// Возвращает название файла для класса
	public function getClassFileName() {
		return $this->getName().'.php';
	}

	// Возвращает название файла для трейта
	public function getTraitFileName() {
		return $this->getName().'Trait.php';
	}

	// Возвращает имя таблицы, но делает первую букву заглавной
	public function getName() {
		return ucfirst($this->name);
	}

	public function getTraitCode($template_string) : string {

		$create_update_params = '';
		$create_update_binds = '';

		$max_column_length = '';
		$comments = '';
		
		$insert_columns = '';
		$insert_values = '';
		
		$update_sets = '';

		// Генерация параметров для функций
		$last_index = array_key_last($this->columns);
		foreach ($this->columns as $column_index => $column) {
			if ($column->isPrimary()) {
				continue;
			}
			$column_name = $column->getName();
			$max_column_length = max(mb_strlen($column_name), $max_column_length);

			$create_update_params .= "\$$column_name";
			$create_update_binds .= "\t\t\$statement->bindValue(':$column_name', \$$column_name);\n";

			$insert_columns .= $column_name;
			$insert_values .= ":$column_name";
			$update_sets .= $column_name.'=:'.$column_name;

			if ($column_index != $last_index) {
				$insert_columns .= ',';
				$insert_values .= ',';
				$update_sets .= ',';
				$create_update_params .= ',';
			}
		}

		// Генерация комментариев
		$comment_line = "\t// %' ".($max_column_length + 1)."s : %s\n";
		foreach ($this->columns as $column) {
			if ($column->isPrimary()) {
				$col_comment = 'Первичный ключ';
			} else {
				$col_comment = $column->getComment();
				if (strlen($col_comment) == 0) {
					$col_comment = 'Комментарий не указан';
				}
			}

			$comments .= sprintf($comment_line,
				$column->getName(),
				$col_comment
			);
		}

		return sprintf($template_string,
			$this->getName().'Trait',
			$this->name,
			$create_update_params,
			$insert_columns,
			$insert_values,
			$create_update_binds,
			$create_update_params,
			$update_sets,
			$create_update_binds,
			$comments
		);
	}

	public function getClassCode($template_string) : string {
		return sprintf($template_string,
			$this->getName(),
			$this->getName().'Trait'
		);
	}
}

class Column {

	public function __construct(
		private string $name,
		private bool $is_primary,
		private string $comment
	) {}

	public function isPrimary() : bool {
		return $this->is_primary;
	}

	// Возвращает название колонки, но добавляет знак доллара перед ним
	public function getNameAsVariable() : string {
		return '$'.$this->name;
	}

	public function getName() : string {
		return $this->name;
	}

	public function getComment() : string {
		return $this->comment;
	}
}


$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

Database::setCustomDsnValues('localhost', 'information_schema');
$db = Database::getConnection();

// Запрос на получение всех таблиц в БД
$all_tables = $db->prepare("
	SELECT `TABLE_NAME` FROM `TABLES` WHERE `TABLE_SCHEMA`=:table_schema");

// Запрос на получение всех колонок таблицы
$all_columns = $db->prepare("
	SELECT `COLUMN_NAME`, `COLUMN_COMMENT`, `COLUMN_KEY`
	FROM `COLUMNS`
	WHERE `TABLE_SCHEMA`=:table_schema AND `TABLE_NAME`=:table_name
	ORDER BY `ORDINAL_POSITION`");

// Привязка данных
$all_tables->bindValue(':table_schema', $_ENV['db_name']);
$all_columns->bindValue(':table_schema', $_ENV['db_name']);
$all_columns->bindParam(':table_name', $table_name);

// Сбор таблиц
$all_tables->execute();
$tables = [];
while (($row_table = $all_tables->fetch()) !== false) {
	$table_name = $row_table['TABLE_NAME'];
	$all_columns->execute();

	// Сбор всех колонок таблицы
	$table_columns = [];
	while (($row_table_columns = $all_columns->fetch()) !== false) {
		$table_columns[] = new Column(
			$row_table_columns['COLUMN_NAME'],
			$row_table_columns['COLUMN_KEY'] === 'PRI',
			$row_table_columns['COLUMN_COMMENT']
		);
	}

	// Добавление модели в массив
	$tables[] = new Table($table_name, $table_columns);
}

define('models_dir', __DIR__.'/../app/Models/');
$trait_template = file_get_contents(__DIR__.'/TraitTemplate.php');
$class_template = file_get_contents(__DIR__.'/ClassTemplate.php');

foreach ($tables as $table) {
	echo "Создание файлов для таблицы: ".$table->getName()."\n";

	$class_filename = models_dir.$table->getClassFileName();
	$trait_filename = models_dir.$table->getTraitFileName();

	if (file_exists($class_filename)) {
		echo "\033[93mВнимание: класс уже существует и не будет перезаписан\033[0m\n";
	} else {
		$fp = fopen($class_filename, 'w');
		fwrite($fp, $table->getClassCode($class_template));
		fclose($fp);
	}

	$fp = fopen($trait_filename, 'w');
	fwrite($fp, $table->getTraitCode($trait_template));
	fclose($fp);

	echo "\033[92mУспешно сгенерировано\033[0m\n";
}
