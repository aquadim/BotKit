<?php
// Модель пользователя
// Хранит в себе объект базы данных

namespace BotKit\Models;

use BotKit\Entities\User as UserEntity;
use BotKit\Enums\State;
use BotKit\Bot;

class User {

    // Ленивая загрузка
    private $lazy_username; // Ник на платформе

    public function __construct(
        protected UserEntity $entity_obj,
        protected string $id_on_platform,
    ) {}

    public function getIdOnPlatform() : string {
        return $this->id_on_platform;
    }

    public function getUsername() {
        if (!isset($this->lazy_username)) {
            $this->lazy_username = Bot::getCurrentDriver()->getUserName();
        }
        return $this->lazy_username;
    }

    public function getState() : State {
        return $this->entity_obj->getState();
    }
}