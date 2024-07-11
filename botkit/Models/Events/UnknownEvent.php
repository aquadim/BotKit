<?php
// Не опознанное драйвером событие

namespace BotKit\Models\Events;

use BotKit\Models\User;
use BotKit\Models\Chats\IChat;

class UnknownEvent implements IEvent {

    public function __construct(
        protected User $user,
        protected IChat $chat,
    ) {}
    
    // Возвращает пользователя, с которым ассоциировано данное событие
    public function getUser() : User {
        return $this->user;
    }

    public function getChat() : IChat {
        return $this->chat;
    }
}