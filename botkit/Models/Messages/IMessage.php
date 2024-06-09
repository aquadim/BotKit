<?php
// Интерфейс сообщения

namespace BotKit\Models\Messages;

interface IMessage {

    // Возвращает ID сообщения
    public function getIdOnPlatform() : string;

    // Возвращает текст сообщения
    public function getText() : string;

    // Возвращает все вложения сообщения
    public function getAttachments() : array;

    // Добавляет клавиатуру к сообщению
    public function addAttachment(IAttachment $attachment) : void;
}