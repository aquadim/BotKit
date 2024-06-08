<?php
// Интерфейс события

namespace BotKit\Models\Events;

use BotKit\Models\User;

interface IEvent {
    
    // Возвращает пользователя, с которым ассоциировано данное событие
    public function getUser() : User;
}