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

enum ColumnType {
	case TrueFalse;
	case Number;
	case Text;
}

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

		return sprintf($template_string,
			$this->getName().'Trait',
			$this->name,
			$create_update_params,
			$insert_columns,
			$insert_values,
			$create_update_binds,
			$create_update_params,
			$update_sets,
			$create_update_binds
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
	private string $name;
	private ColumnType $type;
	private bool $is_primary;
	private bool $is_nullable_or_has_default;
	private string $comment;

	public function __construct(
		string $name,
		ColumnType $type,
		bool $is_primary,
		bool $is_nullable_or_has_default,
		string $comment = ''
	)
	{
		$this->name = $name;
		$this->type = $type;
		$this->is_primary = $is_primary;
		$this->is_nullable_or_has_default = $is_nullable_or_has_default;
		$this->comment = $comment;
	}

	public function isPrimary() : bool {
		return $this->is_primary;
	}

	public function getNameAsVariable() : string {
		return '$'.$this->name;
	}

	public function getName() : string {
		return $this->name;
	}
}

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

Database::setCustomDsnValues('localhost', 'information_schema');
$db = Database::getConnection();

// Получение всех таблиц
$all_tables = $db->prepare("SELECT `TABLE_NAME` FROM `TABLES` WHERE `TABLE_SCHEMA`=:table_schema");
$all_tables->bindValue(':table_schema', $_ENV['db_name']);
$all_tables->execute();
while (($row = $all_tables->fetch()) !== false) {
    var_dump($row);
}
exit();

$models = [];
//~ $models[] = new Table("user", [
	//~ new Column('id', ColumnType::Number, true, false),
	//~ new Column('first_name', ColumnType::Text, false, false),
	//~ new Column('last_name', ColumnType::Text, false, true, 'Last name of the person')
//~ ]);

define('models_dir', __DIR__.'/../app/Models/');
$trait_template = file_get_contents(__DIR__.'/TraitTemplate.php');
$class_template = file_get_contents(__DIR__.'/ClassTemplate.php');

foreach ($models as $model) {
	echo "Создание файлов для модели: ".$model->getName()."\n";

	$class_filename = models_dir.$model->getClassFileName();
	$trait_filename = models_dir.$model->getTraitFileName();

	if (file_exists($class_filename)) {
		echo "Внимание: класс $class_filename уже существует.
		Его обновление приведёт к перезаписи данных. Удалите этот файл, если хотите его обновить\n";
	} else {
		$fp = fopen($class_filename, 'w');
		fwrite($fp, $model->getClassCode($class_template));
		fclose($fp);
	}

	$fp = fopen($class_filename, 'w');
	fwrite($fp, $model->getTraitCode($trait_template));
	fclose($fp);

	echo "Успешно сгенерировано\n";
}
