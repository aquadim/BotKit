<?php
// Класс бота

namespace BotKit;

use BotKit\Drivers\Driver;

class Bot {
	
	private $driver;
	private $driver_loaded = false;

	private User $user;
	private EventData $event_data;

	private $callbacks = [];
	private $fallback;

	private $need_db;

	public function __construct($need_db = true) {
		$this->need_db = $need_db;
	}

	// Загружает драйвер
	public function loadDriver($driver) {
		if ($this->driver_loaded == true) {
			// Драйвер уже выбран
			return;
		}

		if ($driver->willHandleRequest()) {
			$this->driver_loaded = true;
			$this->driver = $driver;
		}
	}

	// Добавляет обработчик события
	public function on(int $event_type, $callback, $match_condition=null, $parameters=null) {
		$this->callbacks[$event_type][] = [$match_condition, $callback];
	}

	// Добавляет обработчик команды (команда - текстовое сообщение)
	public function onCommand(string $commandText, $callback) {
		$this->on(Driver::MSG_PLAIN, $callback, function($u, $e) use ($commandText) {
			return $e->textIs($commandText);
		});
	}

	// Регистрирует обработчик команды с параметрами (например /help {topic})
	// Параметры команды передадутся в обработчик как аргументы функции
	public function onCommandWithParams(string $pattern, $callback) {
		// Определяем что будет параметрами
		$real_pattern = '/^'.preg_replace(
			['/\//','/{(\w+)}/'],
			['\\\/', '(?<$1>.*)'],
			$pattern
		).'$/';
		
		$this->on(Driver::MSG_PLAIN, $callback, function($u, $e, $bot, &$named_groups) use ($real_pattern) {
			echo "Checking pattern: $real_pattern with text: ".$e->getText()."\n";
			return preg_match($real_pattern, $e->getText(), $named_groups);
		});
	}

	// Добавляет обработчик события в том случае, если ни один обработчик не отработал
	public function fallback($callback) {
		$this->fallback = $callback;
	}

	// Проверяет все обработчики и выполняет нужный
	public function handle() {
		if (!$this->driver_loaded) {
			throw new \Exception("Bot has no loaded drivers");
		}

		$will_handle = $this->driver->letHandling();
		if (!$will_handle) {
			exit();
		}

		$this->event_data = $this->driver->getEventData();
		$this->user = $this->driver->getUser($this->event_data);
		
		$this->matchEvents($this->event_data->getEventType());

		// Если скрипт дошёл до этого места, ни один обработчик не отработал
		// Вызываем fallback
		call_user_func($this->fallback, $this->user, $this->event_data, $this->driver);
	}

	// Проверяет все обработчики на соответствие заданным условиям
	// Если обработчик подходит, то он выполняется, а скрипт завершает выполнение
	private function matchEvents($event_type) {
		foreach ($this->callbacks[$event_type] as $callback) {

			// Параметры функции обратного вызова
			// Например при команде /help {topic} этот массив может выглядеть так: ['topic'=>'history']
			// Предопределённые элементы: user, e, drv. Эти параметры всегда передадутся в обработчик
			$named_params = [];

			$check_params = array($this->user, $this->event_data, $this, &$named_params);
			if ($callback[0] !== null && call_user_func_array($callback[0], $check_params) == false) {
				continue;
			}

			// Фильтрование параметров таким образом, чтобы оставить только строковые ключи
			$named_params = array_filter($named_params, function($key) {
				return !is_numeric($key);
			}, ARRAY_FILTER_USE_KEY);

			$named_params['user'] = $this->user;
			$named_params['e'] = $this->event_data;
			$named_params['drv'] = $this->driver;

			var_dump($named_params);

			call_user_func_array($callback[1], $named_params);
			exit();
		}
	}
}