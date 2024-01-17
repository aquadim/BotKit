<?php
// Класс пользователя бота

namespace BotKit;

class User {
	// ID пользователя на платформе
	private $platform_id;

	// Состояние пользователя
	private Enums\FsmState $state;

	// Имя пользователя
	private string $first_name;

	// Фамилия пользователя
	private string $last_name;

	// Связанный объект базы данных
	private $db_object;

	public function __construct($platform_id, $first_name, $last_name, Enums\FsmState $state, $db_object) {
		$this->platform_id = $platform_id;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->state = $state;
		$this->db_object = $db_object;
	}

	// Возвращает состояние пользователя в машине состояний
	public function getState() : Enums\FsmState {
		return $this->state;
	}

	// Возвращает id пользователя на платформе
	public function getPlatformId() {
		return $this->platform_id;
	}
}