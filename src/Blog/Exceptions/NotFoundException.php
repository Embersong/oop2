<?php

namespace Ember\LevelTwo\Blog\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends AppException implements NotFoundExceptionInterface
{

}