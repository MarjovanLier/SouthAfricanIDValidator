<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

#[CoversMethod(SouthAfricanIDValidator::class, 'isValidCitizenshipDigit')]
final class IsValidCitizenshipDigitTest extends TestCase
{
    private const array INVALID_CHARACTERS = [
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
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testValidEleventhCharacter(): void
    {
        foreach (['0', '1', '2'] as $char) {
            $number = '1234567890' . $char;

            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidCitizenshipDigit', [$number]);
            assert(is_bool($result));
            self::assertTrue($result, sprintf('Expected %s to be a valid eleventh character', $char));
        }
    }


    /**
     * Invokes a protected or private method of a class.
     *
     * @param object $object Instantiated object on which to invoke the method.
     * @param string $methodName Method name to invoke.
     * @param array<int, string> $parameters Array of parameters to pass to the method.
     *
     * @return mixed Method return value.
     * @throws ReflectionException
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


    /**
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testInvalidEleventhCharacter(): void
    {
        foreach (self::INVALID_CHARACTERS as $char) {
            // Build a 13-digit number with the test character at position 11
            $testNumber = '1234567890' . $char . '23';
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidCitizenshipDigit', [$testNumber]);
            assert(is_bool($result));
            self::assertFalse($result, sprintf('Expected %s to be an invalid eleventh character', $char));
        }
    }


    /**
     * Test that the method works correctly with exactly 13 digits.
     * Note: The method now assumes the input is already validated to be 13 digits.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testValidLengthIdNumber(): void
    {
        // Test with a 13-digit number with invalid citizenship digit
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidCitizenshipDigit', ['1234567890923']);
        assert(is_bool($result));
        self::assertFalse($result, 'Expected ID with invalid citizenship digit (9) to be invalid');

        // Test with a 13-digit number with valid citizenship digit
        /** @var bool $result */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidCitizenshipDigit', ['1234567890023']);
        self::assertTrue($result, 'Expected ID with valid citizenship digit (0) to be valid');
    }


    /**
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testDifferentiateBetweenTenthAndEleventhCharacter(): void
    {
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'isValidCitizenshipDigit', ['123456789X0']);
        assert(is_bool($result));
        self::assertTrue(
            $result,
            "Valid citizenship digit '0' at position 11 must pass: 123456789X0",
        );
    }
}
