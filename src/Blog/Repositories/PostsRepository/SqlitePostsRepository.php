<?php

namespace Ember\LevelTwo\Blog\Repositories\PostsRepository;

use Ember\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use Ember\LevelTwo\Blog\Exceptions\PostNotFoundException;
use Ember\LevelTwo\Blog\Exceptions\PostsRepositoryException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Post;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Ember\LevelTwo\Blog\UUID;
use PDOException;
use Psr\Log\LoggerInterface;


class SqlitePostsRepository implements PostsRepositoryInterface
{
    private \PDO $connection;
    private LoggerInterface $logger;

    public function __construct(\PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author_uuid, title, text) VALUES (:uuid, :author_uuid, :title, :text)'
        );

        $statement->execute([
            ':uuid' => $post->uuid(),
            ':author_uuid' => $post->getUser()->uuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText()
        ]);

        $this->logger->info("Post created: {$post->uuid()}");
    }


    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);

        return $this->getPost($statement, $uuid);
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException|UserNotFoundException
     */
    private function getPost(\PDOStatement $statement, string $postUuId): Post
    {
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            $message = "Cannot find post: $postUuId";
            $this->logger->warning($message);

            throw new PostNotFoundException($message);
        }

        $userRepository = new SqliteUsersRepository($this->connection);
        $user = $userRepository->get(new UUID($result['author_uuid']));

        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text']
        );

    }

    public function delete(UUID $uuid): void
    {
        try {
            $statement = $this->connection->prepare(
                'DELETE FROM posts WHERE uuid = ?'
            );
            $statement->execute([(string)$uuid]);
        } catch (PDOException $e) {
            throw new PostsRepositoryException(
                $e->getMessage(), (int)$e->getCode(), $e
            );
        }

    }
}