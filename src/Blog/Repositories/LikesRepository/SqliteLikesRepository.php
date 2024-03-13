<?php

namespace Ember\LevelTwo\Blog\Repositories\LikesRepository;

use Ember\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use Ember\LevelTwo\Blog\Exceptions\LikesNotFoundException;
use Ember\LevelTwo\Blog\Like;
use Ember\LevelTwo\Blog\UUID;
use Ember\LevelTwo\Blog\Exceptions\LikeAlreadyExists;


class SqliteLikesRepository implements LikesRepositoryInterface
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Like $like): void
    {
        $statement = $this->connection->prepare('
            INSERT INTO likes (uuid, user_uuid, post_uuid)
            VALUES (:uuid, :user_uuid, :post_uuid)
        ');
        $statement->execute([
            ':uuid' => (string)$like->uuid(),
            ':user_uuid' => (string)$like->getUserId(),
            ':post_uuid' => (string)$like->getPostId(),
        ]);
    }

    /**
     * @throws LikesNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByPostUuid(UUID $uuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE post_uuid = :uuid'
        );

        $statement->execute([
            'uuid' => (string)$uuid
        ]);

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new LikesNotFoundException(
                'No likes to post with uuid = : ' . $uuid
            );
        }

        $likes = [];
        foreach ($result as $like) {
            $likes[] = new Like(
                uuid: new UUID($like['uuid']),
                post_id: new UUID($like['post_uuid']),
                user_id: new UUID($like['user_uuid']),
            );
        }

        return $likes;
    }

    /**
     * @throws LikeAlreadyExists
     */
    public function checkUserLikeForPostExists($postUuid, $userUuid): void
    {
        $statement = $this->connection->prepare(
            'SELECT *
            FROM likes
            WHERE 
                post_uuid = :postUuid AND user_uuid = :userUuid'
        );

        $statement->execute(
            [
                ':postUuid' => $postUuid,
                ':userUuid' => $userUuid
            ]
        );

        $isExisted = $statement->fetch();

        if ($isExisted) {
            throw new LikeAlreadyExists(
                'The users like for this post already exists'
            );
        }
    }
}