<?php
// Модель пользователя
// Хранит в себе объект базы данных

namespace BotKit\Models;

use BotKit\Entities\User as UserEntity;
use BotKit\Enums\State;

class User {
    // Объект сущности Doctrine
    protected UserEntity $entity_obj;

    // Ник
    protected string $username;

    public function __construct(UserEntity $entity_obj, string $username) {
        $this->entity_obj = $entity_obj;
        $this->username = $username;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getState() : State {
        return $this->entity_obj->getState();
    }
}