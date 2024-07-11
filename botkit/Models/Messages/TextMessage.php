<?php
// Класс сообщения

namespace BotKit\Models\Messages;

class TextMessage implements IMessage {

	// ID сообщения
	protected string $id_on_platform;

	// Клавиатура сообщения (если присутствует)
	protected KeyboardAttachment $keyboard;

	// Имеет ли сообщение изображения
	private bool $has_images = false;

	// Имеет ли сообщение клавиатуру
	private bool $has_keyboard = false;

	public function __construct(
		protected string $text = '',
		protected array $attachments = []) {} 

	public function getIdOnPlatform() : string {
		return $this->id_on_platform;
	}

	// Возвращает текст сообщения
	public function getText() : string {
		return $this->text;
	}

	public function getAttachments() : array {
		return $this->attachments;
	}

	public function addAttachment(IAttachment $attachment) : void {
		if (is_a($attachment, KeyboardAttachment::class)) {
			// Используем метод addKeyboard если вложение - клавиатура
			$this->addKeyboard($attachment);
			return;
		}

		$this->attachments[] = $attachment;
	}

	// Добавляет клавиатуру к сообщению
	public function addKeyboard(KeyboardAttachment $keyboard) : void {
		if ($this->has_keyboard == true) {
			throw new Error("keyboard already set");
		}
		$this->has_keyboard = true;
		$this->keyboard = $keyboard;
		$this->attachments[] = $attachment;
	}

	// Добавляет вложение
	public function addImage(ImageAttachment $image) : void {
		$this->has_images = true;
		$this->attachments[] = $image;
	}
}