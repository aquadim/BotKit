<?php
// Не опознанное драйвером событие

namespace BotKit\Models\Events;

use BotKit\Models\User;

class UnknownEvent implements IEvent {

    protected User $user;
    
    // Возвращает пользователя, с которым ассоциировано данное событие
    public function getUser() : User {
        return $this->user;
    }
}