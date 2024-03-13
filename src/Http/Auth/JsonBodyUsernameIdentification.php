<?php

namespace Ember\LevelTwo\Http\Auth;

use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Exceptions\HttpException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\Blog\User;

class JsonBodyUsernameIdentification implements IdentificationInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    )
    {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            // Получаем имя пользователя из JSON-тела запроса;
            // ожидаем, что имя пользователя находится в поле username
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            // Если невозможно получить имя пользователя из запроса -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }
        try {
            // Ищем пользователя в репозитории и возвращаем его
            return $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            // Если пользователь не найден -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }

    }
}