<?php
// Класс события

namespace BotKit;

class EventData {

	// Текст сообщения (если присутствует)
	private string $text;

	// Данные, которые были получены непосредственно сервером
	private $payload;

	// Тип события
	private int $event_type;

	public function __construct($text, $payload, $event_type) {
		$this->text = $text;
		$this->payload = $payload;
		$this->event_type = $event_type;
	}

	// Возвращает объект $payload
	public function getPayload() {
		return $this->payload;
	}

	// Возвращает тип события
	public function getEventType() : int {
		return $this->event_type;
	}

	// Возвращает текст
	public function getText() : string {
		return $this->text;
	}

	// Сравнивает текст с параметром
	public function textIs(string $to_compare) : bool {
		return $to_compare == $this->text;
	}
	
}