<?php

namespace Ember\LevelTwo\Blog\Repositories\UsersRepository;

use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\Person\Name;

class DummyUsersRepository implements UsersRepositoryInterface
{

    public function save(User $user): void
    {
        // TODO: Implement save() method.
    }

    public function get(UUID $uuid): User
    {
        throw new UserNotFoundException("Not found");
    }

    public function getByUsername(string $username): User
    {
        return new User(UUID::random(), new Name("first", "last"), "user123", "123");
    }
}