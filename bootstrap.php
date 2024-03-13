<?php

use Ember\LevelTwo\Blog\Container\DIContainer;
use Ember\LevelTwo\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Ember\LevelTwo\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use Ember\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Ember\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use Ember\LevelTwo\Blog\Repositories\LikesRepository\SqliteLikesRepository;
use Ember\LevelTwo\Http\Auth\AuthenticationInterface;
use Ember\LevelTwo\Http\Auth\BearerTokenAuthentication;
use Ember\LevelTwo\Http\Auth\IdentificationInterface;
use Ember\LevelTwo\Http\Auth\JsonBodyUsernameIdentification;
use Ember\LevelTwo\Http\Auth\JsonBodyUuidIdentification;
use Ember\LevelTwo\Http\Auth\PasswordAuthentication;
use Ember\LevelTwo\Http\Auth\PasswordAuthenticationInterface;
use Ember\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Dotenv\Dotenv;
use Faker\Generator;
use Faker\Provider\Lorem;
use Faker\Provider\ru_RU\Internet;
use Faker\Provider\ru_RU\Person;
use Faker\Provider\ru_RU\Text;

require_once __DIR__ . '/vendor/autoload.php';

// Загружаем переменные окружения из файла .env
Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();

$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/' . $_ENV['SQLITE_DB_PATH'])
);

$logger = (new Logger('blog'));

if ('yes' === $_ENV['LOG_TO_FILES']) {
    $logger->pushHandler(new StreamHandler(
        __DIR__ . '/logs/blog.log'
    ))
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.error.log',
            level: Logger::ERROR,
            bubble: false,
        ));
}

if ('yes' === $_ENV['LOG_TO_CONSOLE']) {
    $logger->pushHandler(
        new StreamHandler("php://stdout")
    );
}

// Создаём объект генератора тестовых данных
$faker = new Generator();
// Инициализируем необходимые нам виды данных
$faker->addProvider(new Person($faker));
$faker->addProvider(new Text($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new Lorem($faker));

// Добавляем генератор тестовых данных
// в контейнер внедрения зависимостей
$container->bind(
    Generator::class,
    $faker
);

$container->bind(
    TokenAuthenticationInterface::class,
    BearerTokenAuthentication::class
);


$container->bind(
    PasswordAuthenticationInterface::class,
    PasswordAuthentication::class
);
$container->bind(
    AuthTokensRepositoryInterface::class,
    SqliteAuthTokensRepository::class
);

$container->bind(
    AuthenticationInterface::class,
    PasswordAuthentication::class
);

$container->bind(
    IdentificationInterface::class,
    JsonBodyUsernameIdentification::class
);

$container->bind(
    LoggerInterface::class,
    $logger

);

$container->bind(
    LikesRepositoryInterface::class,
    SqliteLikesRepository::class
);

$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);

$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);

return $container;
