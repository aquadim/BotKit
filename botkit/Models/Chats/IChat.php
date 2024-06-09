<?php
// Интерфейс чатов

namespace BotKit\Models\Chats;

use BotKit\Entities\Platform;

interface IChat {
    
    // Возвращает платформу чата
    public function getPlatform() : Platform;

    // Возвращает id на платформе
    public function getIdOnPlatform() : string;
}