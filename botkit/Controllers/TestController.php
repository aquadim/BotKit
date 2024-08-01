<?php
// Тестовый контроллер

namespace BotKit\Controllers;

use BotKit\Controller;

class TestController extends Controller {

    public function sendEcho() {
        $user_name = $this->u->getUsername();
        $user_nick = $this->u->getNickname();

        $this->replyDM('Личное сообщение: '.$this->msg_text);
        $this->reply('Сообщение в чат: '.$this->msg_text);
        
        $info = "Ваше имя: $user_name\nВаш ник: @$user_nick";
        $this->reply($info);
    }
    
}