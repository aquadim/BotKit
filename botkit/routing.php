<?php
// Прописывайте свои условия обработки здесь

use BotKit\Bot;
use BotKit\Models\Events\IEvent;
use BotKit\Models\Events\TextMessageEvent;
use BotKit\Enums\State;

Bot::onCommand("/setstate {state_id}", 'TestController@setState');
Bot::onCommand("/info {id}", 'TestController@info');