<?php
// Перечисление для цветов кнопок клавиатуры
// Драйверы могут интерпретировать эти значения как захотят
// включая возможность совсем не интерпретировать

namespace BotKit\Enums;

enum KeyboardButtonColor {
	case Primary;
	case Secondary;
	case Warning;
	case Success;
	case Info;
	case Danger;
	case None;
}