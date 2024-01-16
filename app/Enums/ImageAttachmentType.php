<?php
// Перечисление для режимов прикрепления изображений

namespace BotKit\Enums;

enum ImageAttachmentType {
	case FromFile;
	case FromUrl;
	case FromExisting;
}