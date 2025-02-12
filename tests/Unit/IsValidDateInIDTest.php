<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use UnexpectedValueException;

/**
 * @internal
 * @covers   \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidDateInID
 */
final class IsValidDateInIDTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testValidYymmddDate(): void
    {
        self::assertTrue($this->invokePrivateStaticMethod(['870110xxxxxx']));
        self::assertTrue($this->invokePrivateStaticMethod(['220831xxxxxx']));
    }

    /**
     * @param array<int, string> $parameters
     *
     * @throws ReflectionException
     */
    private function invokePrivateStaticMethod(array $parameters = []): bool
    {
        $reflectionMethod = (new ReflectionClass(SouthAfricanIDValidator::class))->getMethod('isValidDateInID');

        /**
         * @noinspection   PhpExpressionResultUnusedInspection
         * @psalm-suppress UnusedMethodCall
         */
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs(null, $parameters);

        if (!is_bool($result)) {
            // Handle an unexpected type or throw an exception
            throw new UnexpectedValueException('Expected a boolean return value.');
        }

        return $result;
    }

    /**
     * @throws ReflectionException
     */
    public function testInvalidYymmddDate(): void
    {
        self::assertFalse($this->invokePrivateStaticMethod(['871332xxxxxx']), 'Invalid day (32)');
        self::assertFalse($this->invokePrivateStaticMethod(['871313xxxxxx']), 'Invalid month (13)');
        self::assertFalse($this->invokePrivateStaticMethod(['87-01-10xxxxx']), 'Invalid characters');
        self::assertFalse($this->invokePrivateStaticMethod(['xyz123xxxxx']), 'Invalid characters');
    }

    /**
     * @throws ReflectionException
     */
    public function testInvalidDateFormat(): void
    {
        self::assertFalse($this->invokePrivateStaticMethod(['87101xxxxxx']), 'Invalid length (too short)');
    }
}
