<?php
// Интерфейс сообщения

namespace BotKit\Models\Messages;
use BotKit\Models\Chats\IChat;

interface IMessage {
    // Возвращает ID сообщения
    public function getId() : string;
    
    // Устанавливает ID сообщения
    public function setId(string $id) : void;
    
    // Устанавливает чат, в которое было отправлено сообщение
    public function setChat(IChat $chat) : void;
    
    // Возвращает чат, в которое было отправлено сообщение
    public function getChat() : IChat;
}