<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversMethod(SouthAfricanIDValidator::class, 'sanitiseNumber')]
final class SanitiseNumberTest extends TestCase
{
    /**
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testSanitiseNumber(): void
    {
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', ['123-456']);
        self::assertEquals(
            '123456',
            $result,
            "Must remove hyphens from '123-456' and return '123456'",
        );

        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', ['abc123']);
        self::assertEquals(
            '123',
            $result,
            "Must remove letters from 'abc123' and return only digits '123'",
        );

        // Test for already clean number (all digits)
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', ['123456']);
        self::assertEquals(
            '123456',
            $result,
            "Already clean number '123456' must remain unchanged",
        );
    }


    /**
     * Invokes a protected or private method of a class.
     *
     * @param object $object Instantiated object on which to invoke the method.
     * @param string $methodName Method name to invoke.
     * @param array<int, string> $parameters Array of parameters to pass to the method.
     *
     * @return mixed Method return value.
     *
     * @throws \ReflectionException
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflectionMethod = (new ReflectionClass($object::class))->getMethod($methodName);

        /**
         * @noinspection   PhpExpressionResultUnusedInspection
         * @psalm-suppress UnusedMethodCall
         */

        return $reflectionMethod->invokeArgs($object, $parameters);
    }
}
