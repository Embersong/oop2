<?php

namespace Ember\LevelTwo\Http\Actions\Posts;

use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Exceptions\PostNotFoundException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\Http\Actions\ActionInterface;
use Ember\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use Ember\LevelTwo\Http\ErrorResponse;
use Ember\LevelTwo\Http\SuccessfulResponse;
use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\http\Response;

class DeletePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private TokenAuthenticationInterface $authentication,
    )
    {
    }


    public function handle(Request $request): Response
    {
        try {
            $this->authentication->user($request);
        } catch (AuthException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postUuid = $request->query('uuid');
            $this->postsRepository->get(new UUID($postUuid));

        } catch (PostNotFoundException $error) {
            return new ErrorResponse($error->getMessage());
        }

        $this->postsRepository->delete(new UUID($postUuid));

        return new SuccessfulResponse([
            'uuid' => $postUuid,
        ]);
    }
}