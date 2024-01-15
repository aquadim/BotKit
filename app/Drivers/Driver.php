<?php
// Интерфейс для драйверов ботов

namespace BotKit\Drivers;

use BotKit\User;
use BotKit\Message;
use BotKit\EventData;

interface Driver {
	public const MSG_PLAIN		= 0; // Обычное сообщение
	public const MSG_CALLBACK	= 1; // Сообщение обратного вызова
	public const CONFIRMATION	= 2; // Подтверждение сервера
	public const FALLBACK 		= 3; // Тип не определён
	
	// Возвращает данные входящего события
	public function getEventData() : EventData;
	
	// Отправляет текстовое сообщение
	// После отправки в $message следует сохранить id сообщения и вернуть сообщение
	public function sendMessage(User $user, Message $message) : Message;

	// Возвращает объект пользователя
	public function getUser(EventData $event_data) : User;

	// Функция, вызывающаяся при старте обработки запроса
	public function onStart() : void;

	// Возвращает true, если драйвер считает, что запрос можно обработать, иначе false
	public function letHandling() : bool;

	// Возвращает true, если драйвер считает, что ему необходимо обработать этот запрос
	public function willHandleRequest() : bool;

	// Преобразует массив массивов кнопок в разметку клавиатуры
	// Кнопки могут быть вложены в само сообщение или отображены как отдельный виджет
	// Драйверы вправе считывать свойства из клавиатуры, несущие информацию только для них
	public function getKeyboard($kb) : string;
}