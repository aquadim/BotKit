<?php
// Интерфейс для драйверов ботов

namespace BotKit\Drivers;

use BotKit\Models\User as UserModel;
use BotKit\Models\Chats\IChat;
use BotKit\Models\Messages\IMessage;
use BotKit\Models\Events\IEvent;
use BotKit\Models\Events\TextMessageEvent;

interface IDriver {

    // Возвращает true, если драйвер считает, что именно ему необходимо
    // обработать этот запрос.
    public function forThis() : bool;

    // Возвращает событие на основании данных входящего HTTP запроса
    public function getEvent() : IEvent;

    // Возвращает объект пользователя, кто вызвал это событие
    public function getUserModel(string $id_on_platform) : UserModel;

    // Отвечает на текстовое сообщение
    // Если empathise=true, сообщение должно быть явным ответом
    // $e - событие, на которое создаётся ответ
    // $msg - сообщение ответа
    public function reply(
        TextMessageEvent $e,
        IMessage $msg,
        bool $empathise = true) : void;

    // Отсылает сообщение пользователю в личный чат между ботом и пользователем
    public function sendMessage(UserModel $u, IMessage $msg) : void;

    // Изменяет сообщение
    // message_id - ID сообщения на платформе
    // msg - новые данные сообщения
    public function editMessage($message_id, IMessage $msg) : void;

    // Отправляет сообщение в чат
    public function sendToChat(IChat $chat, IMessage $msg) : void;

    // Событие после ensureDriversLoaded
    public function onSelected() : void;
    
    // Событие перед началом обработки запроса
    public function onProcessStart(UserModel $user) : void;

    // Событие завершения обработки
    public function onProcessEnd(UserModel $user) : void;

    // Показывает содержимое переменной (для отладки)
    // $label - что именно за переменная
    // $variable - значение переменной
    public function showContent(string $label, $variable) : void;

    // Возвращает домент платформы бота
    // Например: telegram.org, vk.com, whatsapp.com
    public function getPlatformDomain() : string;
}