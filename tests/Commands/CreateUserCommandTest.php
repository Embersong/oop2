<?php

namespace Ember\LevelTwo\Commands;

use Ember\Blog\UnitTests\DummyLogger;
use Ember\LevelTwo\Blog\Commands\Arguments;
use Ember\LevelTwo\Blog\Commands\CreateUserCommand;
use Ember\LevelTwo\Blog\Commands\Users\CreateUser;
use Ember\LevelTwo\Blog\Exceptions\ArgumentsException;
use Ember\LevelTwo\Blog\Exceptions\CommandException;
use Ember\LevelTwo\Blog\Exceptions\UserNotFoundException;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\DummyUsersRepository;
use Ember\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Ember\LevelTwo\Blog\User;
use Ember\LevelTwo\Blog\UUID;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateUserCommandTest extends TestCase
{

    public function testItSavesUserToRepository(): void
    {
        $usersRepository =  new class implements UsersRepositoryInterface {
// В этом свойстве мы храним информацию о том,
// был ли вызван метод save
            private bool $called = false;

            public function save(User $user): void
            {
// Запоминаем, что метод save был вызван
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {

                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
// Этого метода нет в контракте UsersRepositoryInterface,
// но ничто не мешает его добавить.
// С помощью этого метода мы можем узнать,
// был ли вызван метод save
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreateUser(
            $usersRepository
        );
        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]),
            new NullOutput()
        );
        $this->assertTrue($usersRepository->wasCalled());
    }


    public function testItRequiresLastNameNew(): void
    {
// Тестируем новую команду
        $command = new CreateUser(
            $this->makeUsersRepository(),
        );
// Меняем тип ожидаемого исключения ..
        $this->expectException(RuntimeException::class);
// .. и его сообщение
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "last_name").'
        );
// Запускаем команду методом run вместо handle
        $command->run(
// Передаём аргументы как ArrayInput,
// а не Arguments
// Сами аргументы не меняются
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',
            ]),
// Передаём также объект,
// реализующий контракт OutputInterface
// Нам подойдёт реализация,
// которая ничего не делает
            new NullOutput()
        );
    }

    public function testItRequiresPassword(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepository(),
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: password');
        $command->handle(new Arguments([
            'username' => 'Ivan',
        ]));
    }

    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $command = new CreateUserCommand(new DummyUsersRepository(), new DummyLogger());
        // Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);

        // и его сообщение
        $this->expectExceptionMessage('User already exists: Ivan');

        // Запускаем команду с аргументами
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '123',
        ]));
    }

    // Тест проверяет, что команда действительно требует имя пользователя
    public function testItRequiresFirstName(): void
    {
// $usersRepository - это объект анонимного класса,
// реализующего контракт UsersRepositoryInterface
        $usersRepository = new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
// Ничего не делаем
            }

            public function get(UUID $uuid): User
            {
// И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {

                // И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }
        };
// Передаём объект анонимного класса
// в качестве реализации UsersRepositoryInterface
        $command = new CreateUserCommand($usersRepository, new DummyLogger());
// Ожидаем, что будет брошено исключение
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');
// Запускаем команду
        $command->handle(new Arguments(['username' => 'Ivan', 'password' => '123']));
    }


    // Тест проверяет, что команда действительно требует фамилию пользователя
    public function testItRequiresLastName(): void
    {
// Передаём в конструктор команды объект, возвращаемый нашей функцией
        $command = new CreateUserCommand($this->makeUsersRepository(), new DummyLogger());
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
// Нам нужно передать имя пользователя,
// чтобы дойти до проверки наличия фамилии
            'first_name' => 'Ivan',
            'password' => '123'
        ]));
    }


    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User

            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории
    public function testItSavesUserToRepositoryOld(): void
    {
        // Создаём объект анонимного класса
        $usersRepository = new class implements UsersRepositoryInterface {
// В этом свойстве мы храним информацию о том,
// был ли вызван метод save
            private bool $called = false;

            public function save(User $user): void
            {
// Запоминаем, что метод save был вызван
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {

                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
// Этого метода нет в контракте UsersRepositoryInterface,
// но ничто не мешает его добавить.
// С помощью этого метода мы можем узнать,
// был ли вызван метод save
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreateUserCommand($usersRepository, new DummyLogger());

        // Запускаем команду
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'password' => '123',
        ]));

        $this->assertTrue($usersRepository->wasCalled());
    }

}