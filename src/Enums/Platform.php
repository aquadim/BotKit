<?php
// Перечисление для всех платформ, поддерживаемых ботом
// Этот файл заполняется разработчиком
// Рекомендуемый стиль заполнения: url платформы в CamelCase

namespace BotKit\Enums;

enum Platform : int {
	case TelegramOrg = 0;
	case VkCom = 1;
}