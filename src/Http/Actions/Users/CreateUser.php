<?php

namespace Ember\LevelTwo\Http\Actions\Users;

use Ember\LevelTwo\Blog\Exceptions\HttpException;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\http\Actions\ActionInterface;
use Ember\LevelTwo\http\ErrorResponse;
use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\http\Response;
use Ember\LevelTwo\http\SuccessfulResponse;
use Ember\LevelTwo\Person\Name;

class CreateUser implements ActionInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            $newUserUuid = UUID::random();

            $user = new User(
                $newUserUuid,
                new Name(
                    $request->jsonBodyField('first_name'),
                    $request->jsonBodyField('last_name')
                ),
                $request->jsonBodyField('username')
            );

        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());

        }

        $this->usersRepository->save($user);

        return new SuccessfulResponse([
            'uuid' => (string)$newUserUuid,
        ]);
    }
}