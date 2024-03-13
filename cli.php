<?php


use Ember\LevelTwo\Blog\Commands\FakeData\PopulateDB;
use Ember\LevelTwo\Blog\Commands\Posts\DeletePost;
use Ember\LevelTwo\Blog\Commands\Users\CreateUser;
use Ember\LevelTwo\Blog\Commands\Users\UpdateUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;

$container = require __DIR__ . '/bootstrap.php';

$logger = $container->get(LoggerInterface::class);


// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class,


];

foreach ($commandsClasses as $commandClass) {
// Посредством контейнера
// создаём объект команды
    $command = $container->get($commandClass);
// Добавляем команду к приложению
    $application->add($command);
}

try {

    $application->run();

} catch (Exception $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    echo $e->getMessage();
}
