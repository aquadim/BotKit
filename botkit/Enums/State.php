<?php
// Перечисление для состояний пользователей

namespace BotKit\Enums;

enum State: int {
	// Любое состояние
	case Any = -1;
	// Первое взаимодействие. Не удаляйте!
	case FirstInteraction = 0;
	case Test = 1;
}