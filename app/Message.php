<?php
// Класс сообщения

namespace BotKit;

class Message {

	// ID сообщения
	public $id;

	// Текст сообщения
	private string $text;

	// Клавиатура сообщения (если присутствует)
	private $keyboard;

	public function __construct($text) {
		$this->text = $text;
	}

	public static function create($text="") {
		return new self($text);
	}

	// Возвращает текст сообщения
	public function getText() : string {
		return $this->text;
	}

	public function getKeyboard() {
		return $this->keyboard;
	}

	// Добавляет клавиатуру к сообщению
	public function withKeyboard($keyboard) {
		$this->keyboard = $keyboard;
		return $this;
	}
	
}