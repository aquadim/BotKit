<?php
// Интерфейс события

namespace BotKit\Models\Events;

use BotKit\Models\User;
use BotKit\Models\Chats\IChat;

class TextMessageEvent implements IEvent {

    public function __construct(
        protected string $message_id,
        protected User $user,
        protected IChat $chat,
        protected string $text,
        protected array $attachments,
    ) {}
    
    public function getUser() : User {
        return $this->user;
    }

    public function getChat() : IChat {
        return $this->chat;
    }

    public function getMessageId() : string {
        return $this->message_id;
    }

    public function getText() : string {
        return $this->text;
    }

    public function getAttachments() : array {
        return $this->attachments;
    }
}
