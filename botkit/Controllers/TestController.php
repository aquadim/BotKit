<?php
// Тестовый контроллер

namespace BotKit\Controllers;

use BotKit\Controller;

class TestController extends Controller {

    public function sendEcho() {
        $username = $this->u->getUsername();

        $this->replyDM('DM: '.$this->msg_text);
        $this->reply('CHAT: '.$this->msg_text);
        $this->replyDM("Your username is: @{$username}");
    }
    
}