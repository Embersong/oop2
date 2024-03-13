<?php

namespace Ember\LevelTwo\Http\Actions;

use Ember\LevelTwo\http\Request;
use Ember\LevelTwo\http\Response;

interface ActionInterface
{
    public function handle(Request $request): Response;
}