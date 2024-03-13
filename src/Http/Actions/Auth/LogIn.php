<?php

namespace Ember\LevelTwo\Http\Actions\Auth;

use DateTimeImmutable;
use Ember\LevelTwo\Blog\AuthToken;
use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Ember\LevelTwo\Http\Actions\ActionInterface;
use Ember\LevelTwo\Http\Auth\PasswordAuthenticationInterface;
use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\Http\ErrorResponse;
use Ember\LevelTwo\http\Response;
use Ember\LevelTwo\Http\SuccessfulResponse;

class LogIn implements ActionInterface
{
    public function __construct(
        // Авторизация по паролю
        private PasswordAuthenticationInterface $passwordAuthentication,
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        // Аутентифицируем пользователя
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }
// Генерируем токен
        $authToken = new AuthToken(
// Случайная строка длиной 40 символов
            bin2hex(random_bytes(40)),
            $user->uuid(),
// Срок годности - 1 день
            (new DateTimeImmutable())->modify('+1 day')
        );
// Сохраняем токен в репозиторий
        $this->authTokensRepository->save($authToken);
// Возвращаем токен
        return new SuccessfulResponse([
            'token' => $authToken->token(),
        ]);

    }
}