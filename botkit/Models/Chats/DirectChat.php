<?php
// Чат бота с пользователем мессенджера

namespace BotKit\Models\Chats;

use BotKit\Entities\Platform;

class DirectChat implements IChat {

    public function __construct(
        protected Platform $platform,
        protected string $id_on_platform);
    
    // Возвращает платформу чата
    public function getPlatform() : Platform {
        return $this->platform;
    }

    // Возвращает id на платформе
    public function getIdOnPlatform() : string {
        return $this->id_on_platform;
    }
}