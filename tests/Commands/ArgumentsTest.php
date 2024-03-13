<?php

namespace Ember\LevelTwo\Blog\UnitTests\Commands;

use Ember\LevelTwo\Blog\Commands\Arguments;
use Ember\LevelTwo\Blog\Exceptions\ArgumentsException;
use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
    public function testItReturnsArgumentsValueByName(): void
    {
        // Подготовка
        $arguments = new Arguments(['some_key' => 123]);

        // Действие
        $value = $arguments->get('some_key');

        // Проверка
        $this->assertSame('123', $value);
        $this->assertIsString($value);
    }

    public function testItThrowsAnExceptionWhenArgumentIsAbsent(): void
    {
        // Подготавливаем объект с пустым набором данных
        $arguments = new Arguments([]);

        // Описываем тип ожидаемого исключения
        $this->expectException(ArgumentsException::class);

        // и его сообщение
        $this->expectExceptionMessage("No such argument: some_key");

        // Выполняем действие, приводящее к выбрасыванию исключения
        $arguments->get('some_key');
    }

    /**
     * @dataProvider argumentsProvider
     * @throws ArgumentsException
     */
    public function testItConvertsArgumentsToStrings($inputValue, $expectedValue): void
    {
        $arguments = new Arguments(['some_key' => $inputValue]);
        $value = $arguments->get('some_key');
        $this->assertEquals($expectedValue, $value);
    }

    // Провайдер данных
    public function argumentsProvider(): iterable
    {
        return [
            ['some_string', 'some_string'], // Тестовый набор
            // Первое значение будет передано
            // в тест первым аргументом,
            // второе значение будет передано
            // в тест вторым аргументом
            [' some_string', 'some_string'], // Тестовый набор №2
            [' some_string ', 'some_string'],
            [123, '123'],
            [12.3, '12.3'],
        ];
    }
}