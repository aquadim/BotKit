<?php
// Интерфейс сообщения

namespace BotKit\Models\Messages;

use BotKit\Models\Attachments\PhotoAttachment;
use BotKit\Models\Chats\IChat;
use BotKit\Models\Keyboards\IKeyboard;

interface IMessage {
    // Возвращает ID сообщения
    public function getId() : string;
    
    // Устанавливает ID сообщения
    public function setId(string $id) : void;
    
    // Устанавливает текст сообщения
    public function setText(string $text) : void;
    
    // Возвращает текст сообщения
    public function getText() : string;
    
    // Устанавливает чат, в которое было отправлено сообщение
    public function setChat(IChat $chat) : void;
    
    // Возвращает чат, в которое было отправлено сообщение
    public function getChat() : IChat;
    
    // Добавляет фотографию к сообщению
    public function addPhoto(PhotoAttachment $photo) : void;
    
    // Возвращает все вложения типа фотография
    public function getPhotos() : array;
    
    // Устанавливает клавиатуру
    public function setKeyboard(IKeyboard $keyboard) : void;
    
    // Возвращает клавиатуру
    public function getKeyboard() : IKeyboard;
}