<?php
// Тестовый контроллер

namespace BotKit\Controllers;

use BotKit\Controller;
use BotKit\Enums\State;

class TestController extends Controller {
    
    public function cbHelloWorld() {
        $this->replyText("Привет, мир!");
    }

    public function info($id) {
        $user_name = $this->d->getUserName($id);
        $user_nick = $this->d->getNickName($id);
        
        $message =
        "Информация по пользователю: https://vk.com/$id\n".
        "Имя: $user_name\n".
        "Ник: $user_nick";
        
        $this->replyText($message);
    }
    
    // Устанавливает состояние
    public function setState($state_id) {
        $new_state = State::tryFrom($state_id);
        if ($new_state === null) {
            $this->reply("Не найдено состояние с ID ".$state_id);
            return;
        }
        
        $this->u->setState($new_state);
        $this->replyText("Установлено состояние ".$new_state->name);
    }
    
    public function fallback() {
        $this->replyText("Fallback");
    }
}
