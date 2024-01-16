<?php
// Изображение, вложенное в сообщение

namespace BotKit\Attachments;

use BotKit\Enums\ImageAttachmentType;

class ImageAttachment {
	// Тип прикрепления
	private ImageAttachmentType $type;

	// Что прикрепляется (интерпретируется с помощью драйверов)
	private $value;

	public function __construct($value, $type) {
		$this->value = $value;
		$this->type = $type;
	}

	// Указывает, что изображение прикрепляется из файла на диске
	public static function fromFile($filename) {
		return new self($filename, ImageAttachmentType::FromFile);
	}
	
	// Указывает, что изображение прикрепляется из веб-ресурса
	public static function fromUrl($url) {
		return new self($url, ImageAttachmentType::FromUrl);
	}
	
	// Указывает, что прикрепляемое изображение уже существует на целевом сервере
	public static function fromExisting($image) {
		return new self($image, ImageAttachmentType::FromExisting);
	}

	public function getType() {
		return $this->type;
	}

	public function getValue() {
		return $this->value;
	}
	
	
}