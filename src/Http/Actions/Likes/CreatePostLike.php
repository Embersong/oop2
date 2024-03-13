<?php

namespace Ember\LevelTwo\Http\Actions\Likes;

use Ember\LevelTwo\Blog\Exceptions\AuthException;
use Ember\LevelTwo\Blog\Exceptions\HttpException;
use Ember\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use Ember\LevelTwo\Blog\Exceptions\LikeAlreadyExists;
use Ember\LevelTwo\Blog\Exceptions\PostNotFoundException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Like;
use Ember\LevelTwo\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use Ember\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\Http\Actions\ActionInterface;
use Ember\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use Ember\LevelTwo\Http\ErrorResponse;
use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\http\Response;
use Ember\LevelTwo\Http\SuccessfulResponse;

class CreatePostLike implements ActionInterface
{
    public   function __construct(
        private LikesRepositoryInterface $likesRepository,
        private PostsRepositoryInterface $postRepository,
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
            $author = $this->authentication->user($request);
        } catch (AuthException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postUuid = $request->JsonBodyField('post_uuid');
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }


        try {
            $this->postRepository->get(new UUID($postUuid));
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->likesRepository->checkUserLikeForPostExists($postUuid, $author->uuid());
        } catch (LikeAlreadyExists $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikeUuid = UUID::random();

        $like = new Like(
            uuid: $newLikeUuid,
            post_id: new UUID($postUuid),
            user_id: $author->uuid(),

        );

        $this->likesRepository->save($like);

        return new SuccessFulResponse(
            ['uuid' => (string)$newLikeUuid]
        );
    }
}