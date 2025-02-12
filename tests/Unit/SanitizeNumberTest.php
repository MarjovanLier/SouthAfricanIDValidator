<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::sanitizeNumber
 */
final class SanitizeNumberTest extends TestCase
{
    public function testSanitizeNumber(): void
    {
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['123-456']);
        self::assertEquals('123456', $result);

        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['abc123']);
        self::assertEquals('123', $result);

        // Add test for already clean number (all digits)
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['123456']);
        self::assertEquals('123456', $result);
    }


    /**
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call.
     * @param array<int, string> $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
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
}
