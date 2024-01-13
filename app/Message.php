<?php
// Класс сообщения

namespace BotKit;

class Message {

	// ID сообщения
	public $id;

	// Текст сообщения (если присутствует)
	private string $text;

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
	
}