<?php

namespace Ember\LevelTwo\Blog\Repositories\UsersRepository;

use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Blog\UUID;

interface UsersRepositoryInterface
{
    public function save(User $user): void;
    public function get(UUID $uuid): User;
    public function getByUsername(string $username): User;
}