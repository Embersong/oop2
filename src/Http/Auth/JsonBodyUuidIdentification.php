<?php

namespace Ember\LevelTwo\Http\Auth;

use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Exceptions\HttpException;
use Ember\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\Http\Request;

class JsonBodyUuidIdentification implements IdentificationInterface
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
            // Получаем UUID пользователя из JSON-тела запроса;
            // ожидаем, что корректный UUID находится в поле user_uuid
            $userUuid = new UUID($request->jsonBodyField('user_uuid'));
        } catch (HttpException|InvalidArgumentException $e) {
            // Если невозможно получить UUID из запроса -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }
        try {
            // Ищем пользователя в репозитории и возвращаем его
            return $this->usersRepository->get($userUuid);
        } catch (UserNotFoundException $e) {
            // Если пользователь с таким UUID не найден -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }
    }
}