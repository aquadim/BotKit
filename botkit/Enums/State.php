<?php
// Перечисление для состояний пользователей

namespace BotKit\Enums;

enum State: int {
	// Первое взаимодействие. Не удаляйте!
	case FirstInteraction = 0;
}