<?php

namespace Ember\LevelTwo\Blog\Repositories\LikesRepository;

use Ember\LevelTwo\Blog\Like;
use Ember\LevelTwo\Blog\UUID;

interface LikesRepositoryInterface
{
    public function save(Like $like) : void;
    public function getByPostUuid(UUID $uuid) : array;
}