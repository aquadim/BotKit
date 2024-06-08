<?php
// Интерфейс для драйверов ботов

namespace BotKit\Drivers;

use BotKit\Models\User;
use BotKit\Models\Chats\Chat;
use BotKit\Models\Messages\TextMessage;
use BotKit\Models\Events\Event;
use BotKit\Models\Events\TextMessageEvent;

interface IDriver {

    // Возвращает true, если драйвер считает, что именно ему необходимо
    // обработать этот запрос.
    public function forThis() : bool;

    // Возвращает событие на основании данных входящего HTTP запроса
    public function getEvent() : Event;

    // Возвращает объект пользователя, кто вызвал это событие
    public function getUser() : User;

    // Отвечает на текстовое сообщение
    // Если empathise=true, сообщение должно быть явным ответом
    // $e - событие, на которое создаётся ответ
    // $msg - сообщение ответа
    public function reply(
        TextMessageEvent $e,
        Message $msg,
        bool $empathise = true) : void;

    // Отсылает сообщение пользователю в личный чат между ботом и пользователем
    public function sendMessage(User $u, Message $msg) : void;

    // Изменяет сообщение
    // message_id - ID сообщения на платформе
    // msg - новые данные сообщения
    public function editMessage($message_id, Message $msg) : void;

    // Отправляет сообщение в чат
    public function sendToChat(Chat $chat, Message $msg);

    // Возвращает ник пользователя, например @aquadim
    public function getUserNick(User $u) : string;

    // Событие перед началом обработки запроса
    public function onProcessStart() : void;

    // Событие после ensureDriversLoaded
    public function onSelected() : void;

    // Событие завершения обработки
    public function onProcessEnd() : void;

    // Событие сохранения пользователя в БД
    public function onUserSave(User $user) : void;

    // Показывает содержимое переменной (для отладки)
    // $label - что именно за переменная
    // $variable - значение переменной
    public function showContent(string $label, $variable) : void;

    // Возвращает домент платформы бота
    // Например: telegram.org, vk.com, whatsapp.com
    public function getPlatformDomain() : string;
}