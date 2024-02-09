<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidLuhnChecksum
 */
final class IsValidLuhnChecksumTest extends TestCase
{
    /**
     * Provides a set of valid Luhn numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'79927398713'}, list{'1234567812345670'}, list{'0'}, list{'18'}, list{'26'}, list{'34'},
     *     list{'42'}, list{'59'}, list{'67'}, list{'75'}, list{'83'}, list{'91'}, list{'109'}, list{'117'},
     *     list{'125'}, list{'133'}, list{'141'}, list{'158'}, list{'166'}, list{'174'}, list{'182'}, list{'190'}}
     */
    public static function provideValidLuhnNumbers(): array
    {
        return [
            ['79927398713'],
            ['1234567812345670'],
            ['0'],
            ['18'],
            ['26'],
            ['34'],
            ['42'],
            ['59'],
            ['67'],
            ['75'],
            ['83'],
            ['91'],
            ['109'],
            ['117'],
            ['125'],
            ['133'],
            ['141'],
            ['158'],
            ['166'],
            ['174'],
            ['182'],
            ['190'],
        ];
    }


    /**
     * Provides a set of invalid Luhn numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'79927398714'}, list{'1234567812345678'}, list{'0a027398714'}, list{'79927a398714'},
     *     list{'a123456781234567'}, list{'123456781234567a'}, list{'25'}, list{'1'}, list{'2'}, list{'3'}, list{'4'},
     *     list{'5'}, list{'6'}, list{'7'}, list{'8'}, list{'9'}, list{'10'}, list{'11'}, list{'12'}, list{'13'},
     *     list{'14'}, list{'15'}, list{'16'}, list{'17'}, list{'19'}, list{'20'}, list{'21'}, list{'191'},
     *     list{'192'}, list{'193'}, list{'194'}, list{'195'}, list{'196'}, list{'197'}, list{'198'}, list{'199'},
     *     list{'241'}, list{'242'}}
     */
    public static function provideInvalidLuhnNumbers(): array
    {
        return [
            ['79927398714'],
            ['1234567812345678'],
            ['0a027398714'],
            ['79927a398714'],
            ['a123456781234567'],
            ['123456781234567a'],
            ['25'],
            ['1'],
            ['2'],
            ['3'],
            ['4'],
            ['5'],
            ['6'],
            ['7'],
            ['8'],
            ['9'],
            ['10'],
            ['11'],
            ['12'],
            ['13'],
            ['14'],
            ['15'],
            ['16'],
            ['17'],
            ['19'],
            ['20'],
            ['21'],
            ['191'],
            ['192'],
            ['193'],
            ['194'],
            ['195'],
            ['196'],
            ['197'],
            ['198'],
            ['199'],
            ['241'],
            ['242'],
        ];
    }


    /**
     * @dataProvider provideValidLuhnNumbers
     *
     * @throws ReflectionException
     */
    public function testValidLuhnNumbers(string $number): void
    {
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);

        self::assertTrue($result);
    }


    /**
     * @dataProvider provideInvalidLuhnNumbers
     *
     * @throws ReflectionException
     */
    public function testInvalidLuhnNumbers(string $number): void
    {
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertFalse($result);
    }


    /**
     * Additional specific tests to cover edge cases and ensure numeric strings are handled correctly.
     *
     * @throws ReflectionException
     */
    public function testEdgeCasesAndNumericStrings(): void
    {
        // Numeric string that is a valid Luhn number
        $validNumericString = '1234567812345670';
        $invalidNumericString = '1234567812345678';
        $nonNumericString = '123abc';
        $anotherInvalidNumber = '4561231231234';

        self::assertTrue(
            $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$validNumericString]),
            sprintf("Expected numeric string '%s' to be valid according to Luhn, but it failed.", $validNumericString)
        );

        self::assertFalse(
            $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$invalidNumericString]),
            sprintf(
                "Expected numeric string '%s' to be invalid according to Luhn, but it passed.",
                $invalidNumericString
            )
        );

        self::assertFalse(
            $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$nonNumericString]),
            sprintf("Expected non-numeric string '%s' to fail Luhn validation, but it passed.", $nonNumericString)
        );

        self::assertFalse(
            $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$anotherInvalidNumber]),
            sprintf("Expected '%s' to be invalid according to Luhn, but it passed.", $anotherInvalidNumber)
        );
    }


    /**
     * @dataProvider provideValidLuhnNumbers
     *
     * @throws ReflectionException
     */
    public function testValidLuhnNumbersWithCastIntMutation(string $number): void
    {
        // Test to catch CastInt mutation
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertTrue(
            $result,
            sprintf("Number '%s' should be valid. Failure may indicate issues with integer casting.", $number)
        );
    }


    /**
     * Specific test for GreaterThan mutation.
     *
     * @throws ReflectionException
     */
    public function testGreaterThanMutation(): void
    {
        $number = '18';
        // Choosing a number that, when processed, would not be affected by the >= mutation
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertTrue(
            $result,
            sprintf("Number '%s' doubling to 9 should not have 9 subtracted. Mutation may alter this logic.", $number)
        );
    }


    /**
     * Specific test for PlusEqual mutation.
     *
     * @throws ReflectionException
     */
    public function testPlusEqualMutation(): void
    {
        $number = '79927398713';
        // Known valid Luhn number
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertTrue($result, 'Incorrect sum calculation. Mutation changing += to -= could cause this failure.');
    }


    /**
     * @throws ReflectionException
     */
    public function testIsValidLuhnChecksumHandlesStringDigitsAsIntegers(): void
    {
        // Use a number where not casting to int would fail the Luhn check due to string concatenation instead of arithmetic addition.
        $number = '4111111111111111';
        // A valid Visa credit card number
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        $this->assertTrue($result, 'Failed to handle string digits as integers correctly.');
    }


    /**
     * @throws ReflectionException
     */
    public function testIsValidLuhnChecksumWithExactDoublingToNine(): void
    {
        // Use a number that includes a digit doubling to 9, which should not have 9 subtracted under the correct logic.
        $number = '091';
        // A simple case where the middle digit (doubled) would be affected by the mutation
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        $this->assertTrue($result, 'Incorrectly handled a digit doubling to 9 due to >= mutation.');
    }


    /**
     * @throws ReflectionException
     */
    public function testIsValidLuhnChecksumWithAdditionSubstitution(): void
    {
        // Use a number that is known to be valid under the Luhn algorithm
        $number = '79927398713';
        // A classic example used in Luhn algorithm demonstrations
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        $this->assertTrue(
            $result,
            'Summing error, possibly due to incorrect substitution of addition with subtraction.'
        );
    }


    /**
     * @throws ReflectionException
     */
    private function getPrivateMethod(): ReflectionMethod
    {
        $reflectionMethod = (new ReflectionClass(SouthAfricanIDValidator::class))->getMethod('isValidLuhnChecksum');

        /**
         * @noinspection PhpExpressionResultUnusedInspection
         *
         * @psalm-suppress UnusedMethodCall
         */
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }
}
