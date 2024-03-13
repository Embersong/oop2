<?php

namespace Ember\LevelTwo\Http\Auth;

use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Http\Request;

interface AuthenticationInterface
{
    public function user(Request $request): User;
}