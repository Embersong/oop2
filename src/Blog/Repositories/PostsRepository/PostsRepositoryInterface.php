<?php

namespace Ember\LevelTwo\Blog\Repositories\PostsRepository;

use Ember\LevelTwo\Blog\Post;
use Ember\LevelTwo\Blog\UUID;

interface PostsRepositoryInterface
{
    public function save(Post $post): void;
    public function get(UUID $uuid): Post;
    public function delete(UUID $uuid): void;
}