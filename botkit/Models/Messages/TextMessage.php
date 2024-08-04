<?php
// Класс исходящего сообщения

namespace BotKit\Models\Messages;
use BotKit\Models\Attachments\PhotoAttachment;
use BotKit\Models\Chats\IChat;

class TextMessage implements IMessage {

	// ID сообщения
	protected string $id;
	
	// Текст сообщения
	protected string $text;
	
	// Вложения: изображения в сообщении
	protected array $photos;
	
	// Чат, в который было отправлено сообщение
	protected IChat $chat;

	public function __construct($text, $photos) {
		$this->text = $text;
		$this->photos = $photos;
	}

	// Создаёт сообщение с текстом $text
	public static function create(string $text) : TextMessage {
		return new TextMessage($text, []);
	}
	
	// Устанавливает id сообщения
	public function setId(string $id) : void {
		$this->id = $id;
	}

	// Возвращает id сообщения
	public function getId() : string {
		return $this->id;
	}

	// Возвращает текст сообщения
	public function getText() : string {
		return $this->text;
	}
	
	// Возвращает фото
	public function getPhotos() : array {
		return $this->photos;
	}

	// Добавляет вложение
	public function addPhoto(PhotoAttachment $photo) : void {
		$this->photos[] = $photo;
	}
	
	public function setChat(IChat $chat) : void {
		$this->chat = $chat;
	}
	
	public function getChat() : IChat {
		return $this->chat;
	}
}