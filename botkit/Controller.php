<?php
// Родительский класс контроллеров

namespace BotKit;

use BotKit\Models\Events\IEvent;
use BotKit\Drivers\IDriver;
use BotKit\Models\User;
use BotKit\Models\Messages\IMessage;
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
    protected function replyTextDM(string $text) {
        $this->d->sendDirectMessage($this->u, new TextMessage($text, []));
    }

    // Помощник: отправляет текстовое сообщение В ЧАТ, где было вызвано событие
    protected function replyText(string $text) {
        $this->d->sendToChat($this->e->getChat(), new TextMessage($text, []));
    }
    
    // Отправляет текстовое сообщение ПОЛЬЗОВАТЕЛЮ, вызвавшему сообщение
    protected function replyDM(TextMessage $msg) {
        $this->d->sendDirectMessage($this->u, $msg);
    }
     
    // Отвечает текстовое сообщение В ЧАТ
    protected function reply(TextMessage $msg) {
        $this->d->sendToChat($this->e->getChat(), $msg);
    }
    
    // Редактирует сообщение
    protected function edit(IMessage $old, IMessage $new) {
        $this->d->editMessage($old, $new);
    }
    
    // Устанавливает для сообщения id ответа из входящего события
    // Событие обязано присылать сообщение.
    protected function setReplyIdFor(IMessage $msg) {
        if (!is_a($this->e, TextMessage::class)) {
            throw new \Exception("Current event did not send message to reply for");
        }
        $msg->setReplyId($this->e->getMessageId());
    }
}
