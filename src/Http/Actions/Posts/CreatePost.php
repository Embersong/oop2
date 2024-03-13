<?php

namespace Ember\LevelTwo\Http\Actions\Posts;

use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Post;
use Ember\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\Blog\Exceptions\HttpException;
use Ember\LevelTwo\http\Actions\ActionInterface;
use Ember\LevelTwo\Http\Auth\AuthenticationInterface;
use Ember\LevelTwo\Http\Auth\IdentificationInterface;
use Ember\LevelTwo\Http\Auth\JsonBodyUsernameIdentification;
use Ember\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use Ember\LevelTwo\http\ErrorResponse;
use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\http\Response;
use Ember\LevelTwo\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreatePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
// Внедряем контракт логгера
        private LoggerInterface $logger,
        private TokenAuthenticationInterface $authentication,


    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {

        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }


        $newPostUuid = UUID::random();

        try {
            $post = new Post(
                $newPostUuid,
                $user,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $this->postsRepository->save($post);
        $this->logger->info("Post created: $newPostUuid");

        return new SuccessfulResponse([
            'uuid' => (string)$newPostUuid,
        ]);
    }
}