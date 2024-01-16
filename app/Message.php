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

	// Вложения сообщения
	private array $image_attachments = [];

	// Имеет ли сообщение изображения
	private bool $has_images = false;

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

	// Добавляет вложение
	public function withImage($image) {
		$this->has_images = true;
		$this->image_attachments[] = $image;
		return $this;
	}

	// Возвращает массив всех сохранённых изображений
	public function getImages() {
		return $this->image_attachments;
	}

	// Возвращает true если вложения сообщения содержат изображения
	public function hasImages() : bool {
		return $this->has_images;
	}
	
}