<?php
// Интерфейс события

namespace BotKit\Models\Events;

use BotKit\Models\User;

interface IEvent {
    
    // Возвращает пользователя, с которым ассоциировано данное событие
    public function getUser() : User;

    // Возвращает чат, из которого был вызвано это событие
    public function getChat() : IChat;
}