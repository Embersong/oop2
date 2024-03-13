<?php


use Ember\LevelTwo\Blog\Exceptions\AppException;
use Ember\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Ember\LevelTwo\Http\Actions\Auth\LogIn;
use Ember\LevelTwo\Http\Actions\Auth\LogOut;
use Ember\LevelTwo\Http\Actions\Posts\CreatePost;
use Ember\LevelTwo\Http\Actions\Users\CreateUser;
use Ember\LevelTwo\Http\Actions\Users\FindByUsername;
use Ember\LevelTwo\Http\ErrorResponse;
use Ember\LevelTwo\Http\Request;
use Ember\LevelTwo\Http\SuccessfulResponse;
use Ember\LevelTwo\Http\Actions\Posts\DeletePost;
use Ember\LevelTwo\Http\Actions\Likes\CreatePostLike;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);

try {
    $path = $request->path();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}

try {
    $method = $request->method();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}


$routes = [
    'GET' => [
        '/users/show' => FindByUsername::class,
    ],
    'POST' => [
        '/login' => LogIn::class,
        '/logout' => LogOut::class,
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/post-likes/create' => CreatePostLike::class,
    ],
    'DELETE' => [
        '/posts' => DeletePost::class,
    ],

];

if (!array_key_exists($method, $routes) || !array_key_exists($path, $routes[$method])) {
// Логируем сообщение с уровнем NOTICE
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

$actionClassName = $routes[$method][$path];

$action = $container->get($actionClassName);

try {
    $response = $action->handle($request);

} catch (AppException $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    (new ErrorResponse($e->getMessage()))->send();
    return;
}

$response->send();