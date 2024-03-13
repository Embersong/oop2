<?php

namespace Ember\LevelTwo\Blog\Commands;

use Ember\LevelTwo\Blog\Exceptions\ArgumentsException;
use Ember\LevelTwo\Blog\Exceptions\CommandException;
use Ember\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\Person\Name;
use Psr\Log\LoggerInterface;

//php cli.php username=ivan first_name=Ivan last_name=Nikitin password=123

class CreateUserCommand
{

// Команда зависит от контракта репозитория пользователей,
// а не от конкретной реализации
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws CommandException
     * @throws InvalidArgumentException|ArgumentsException
     */
    public function handle(Arguments $arguments): void
    {
        $this->logger->info("Create user command started");

        $username = $arguments->get('username');



// Проверяем, существует ли пользователь в репозитории
        if ($this->userExists($username)) {
            $this->logger->warning("User already exists: $username");
// Бросаем исключение, если пользователь уже существует
            throw new CommandException("User already exists: $username");
        }

        $user = User::createFrom(
            $username,
            $arguments->get('password'),
            new Name(
                $arguments->get('first_name'),
                $arguments->get('last_name')
            )
        );


        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save($user);

        $this->logger->info("User created: " . $user->uuid());
    }

    private function userExists(string $username): bool
    {
        try {
        // Пытаемся получить пользователя из репозитория
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }



}