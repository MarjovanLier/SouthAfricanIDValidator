<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidEleventhCharacter
 */
final class IsValidEleventhCharacterTest extends TestCase
{
    /**
     * @var string[]
     */
    private const INVALID_CHARACTERS = [
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'A',
        'B',
        'Z',
    ];

    /**
     * @var string
     */
    private const NUMBER = '123456789';


    public function testValidEleventhCharacter(): void
    {
        foreach (['0', '1', '2'] as $char) {
            $number = '1234567890' . $char;

            /**
             * @var bool $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidEleventhCharacter', [$number]);
            self::assertTrue($result, sprintf('Expected %s to be a valid eleventh character', $char));
        }
    }


    /**
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call.
     * @param array<int, string> $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     *
     * @throws ReflectionException
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflectionMethod = (new ReflectionClass($object::class))->getMethod($methodName);

        /**
         * @noinspection PhpExpressionResultUnusedInspection
         *
         * @psalm-suppress UnusedMethodCall
         */
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $parameters);
    }


    public function testInvalidEleventhCharacter(): void
    {
        foreach (self::INVALID_CHARACTERS as $char) {
            /**
             * @var bool $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidEleventhCharacter', [self::NUMBER]);
            self::assertFalse($result, sprintf('Expected %s to be an invalid eleventh character', $char));
        }
    }


    public function testShortIDNumber(): void
    {
        /**
         * @var bool $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidEleventhCharacter', ['123456789']);
        self::assertFalse($result, 'Expected short ID number to be invalid');
    }


    public function testDifferentiateBetweenTenthAndEleventhCharacter(): void
    {
        /**
         * @var bool $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidEleventhCharacter', ['123456789X0']);
        self::assertTrue($result);
    }
}
