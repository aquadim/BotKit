<?php
// Событие обратного вызова

namespace BotKit\Models\Events;

use BotKit\Models\User;
use BotKit\Models\Chats\IChat;
use BotKit\Enums\CallbackType;

class CallbackEvent implements IEvent {
    
    public function __construct(
        protected string $message_id,
        protected User $user,
        protected IChat $chat,
        protected CallbackType $type,
        protected array $data
    ) {}
    
    public function getUser() : User {
        return $this->user;
    }

    public function getChat() : IChat {
        return $this->chat;
    }
    
    public function getText() : string {
        return "";
    }
    
    public function getCallbackType() : CallbackType {
        return $this->type;
    }
    
    public function getCallbackData() : array {
        return $this->data;
    }
}
