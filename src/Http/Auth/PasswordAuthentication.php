<?php

namespace Ember\LevelTwo\Http\Auth;

use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Exceptions\HttpException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Http\Request;

class PasswordAuthentication implements PasswordAuthenticationInterface
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
        // 1. Идентифицируем пользователя
        try {
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }
        try {
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }
// 2. Аутентифицируем пользователя
// Проверяем, что предъявленный пароль
// соответствует сохранённому в БД
        try {
            $password = $request->jsonBodyField('password');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }


        if (!$user->checkPassword($password)) {
// Если пароли не совпадают — бросаем исключение
            throw new AuthException('Wrong password');
        }

// Пользователь аутентифицирован
        return $user;
    }

}