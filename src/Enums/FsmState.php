<?php
// Перечисление для состояний пользователей
// Этот файл заполняется разработчиком

namespace BotKit\Enums;

enum FsmState : int {
	case Undefined = 0;
	case HelloWorld = 1;
}