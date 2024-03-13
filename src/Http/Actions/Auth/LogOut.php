<?php

namespace Ember\LevelTwo\Http\Actions\Auth;

use Ember\LevelTwo\Blog\AuthToken;
use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Exceptions\AuthTokenNotFoundException;
use Ember\LevelTwo\Blog\Exceptions\HttpException;
use Ember\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Ember\LevelTwo\HTTP\Actions\ActionInterface;
use Ember\LevelTwo\Http\Auth\BearerTokenAuthentication;
use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\http\Response;
use Ember\LevelTwo\Http\SuccessfulResponse;

class LogOut implements ActionInterface
{

    public function __construct(
        private AuthTokensRepositoryInterface $authTokensRepository,
        private BearerTokenAuthentication $authentication
    ) {
    }

    /**
     * @throws AuthException
     */
    public function handle(Request $request): Response
    {
       $token = $this->authentication->getAuthTokenString($request);

        try {
            $authToken = $this->authTokensRepository->get($token);
        } catch (AuthTokenNotFoundException $exception) {
            throw new AuthException($exception->getMessage());
        }

        $authToken->setExpiresOn(new \DateTimeImmutable("now"));


        $this->authTokensRepository->save($authToken);

        return new SuccessfulResponse([
            'token' => $authToken->token()
        ]);

    }
}