<?php
// Перечисление для типов обратного вызова

namespace BotKit\Enums;

enum CallbackType: int {
    case HelloWorld = 0;
    case YoutubeShowPreview = 1;
}
