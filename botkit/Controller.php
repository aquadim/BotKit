<?php
// Родительский класс контроллеров

namespace BotKit;

use BotKit\Models\Events\IEvent;
use BotKit\Drivers\IDriver;
use BotKit\Models\User;
use BotKit\Models\Messages\TextMessage;

class Controller {
    // Событие, которое сейчас обрабатывается
    protected IEvent $e;

    // Драйвер, который сейчас задействован
    protected IDriver $d;

    // Пользователь, вызвавший событие
    protected User $u;

    // Текст сообщения (если есть)
    protected string $msg_text;

    // Внедрение зависимостей
    public function init(IEvent $event, IDriver $driver) {
        $this->e = $event;
        $this->d = $driver;
        $this->u = $event->getUser();
        $this->msg_text = $event->getText();
    }

    // Помощник: отправляет текстовое сообщение ПОЛЬЗОВАТЕЛЮ, вызвавшему событие
    protected function replyDM(string $text) {
        $this->d->sendDirectMessage($this->u, new TextMessage($text));
    }

    // Помощник: отправляет текстовое сообщение В ЧАТ, где было вызвано событие
    protected function reply(string $text) {
        $this->d->sendToChat($this->e->getChat(), new TextMessage($text));
    }
}