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
	public function on(int $event_type, $callback, $match_condition = null) {
		$this->callbacks[$event_type][] = [$match_condition, $callback];
	}

	// Добавляет обработчик команды (команда - текстовое сообщение)
	public function onCommand(string $commandText, $callback) {
		$this->on(Driver::MSG_PLAIN, $callback, function($u, $e) use ($commandText) {
			return $e->textIs($commandText);
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
			if ($callback[0] === null) {
				// Условие выполнения не было указано, это значит что оно всегда истино
				$check = true;
			} else {
				$check = call_user_func($callback[0], $this->user, $this->event_data, $this);
			}

			if ($check == true) {
				call_user_func($callback[1], $this->user, $this->event_data, $this->driver);
				exit();
			}
		}
	}
}