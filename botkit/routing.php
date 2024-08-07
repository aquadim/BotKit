<?php
// Прописывайте свои условия обработки здесь

use BotKit\Bot;
use BotKit\Models\Events\IEvent;
use BotKit\Models\Events\TextMessageEvent;
use BotKit\Enums\State;

Bot::onCommand("/setstate {state_id}", 'TestController@setState');
Bot::onCommand("/info {id}", 'TestController@info');

Bot::onCommand("/thispersondoesnotexist {count}", "ImageController@thispersondoesnotexist");
Bot::onCommand("/gravatar {email} {size}", "ImageController@gravatar");
Bot::onCommand("/ytPreview {yt_id}", "ImageController@ytPreview");

Bot::onCommand("/clearKeyboard", "KeyboardController@clearKeyboard");
Bot::onCommand("/keyboard", "KeyboardController@getTestKeyboard");
Bot::onCommand("/keyboardInline", "KeyboardController@getTestKeyboardInline");
Bot::onCommand("/ytKeyboard {yt_id}", "KeyboardController@getYTThumbnailLink");