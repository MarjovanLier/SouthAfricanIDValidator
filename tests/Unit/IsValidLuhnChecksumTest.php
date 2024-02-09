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
     * @psalm-return list{list{'1234567812345670'}, list{'26'}, list{'34'}, list{'42'}, list{'59'}, list{'67'},
     *     list{'75'}, list{'83'}, list{'91'}, list{'109'}, list{'117'}, list{'125'}, list{'133'}, list{'141'},
     *     list{'158'}, list{'166'}, list{'174'}, list{'182'}, list{'190'}}
     */
    public static function provideValidLuhnNumbers(): array
    {
        return [
            ['1234567812345670'],
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
     * @psalm-return list{list{'1234567812345678'}, list{'0a027398714'}, list{'79927a398714'},
     *     list{'a123456781234567'}, list{'123456781234567a'}, list{'25'}, list{'1'}, list{'2'}, list{'3'}, list{'4'},
     *     list{'5'}, list{'6'}, list{'7'}, list{'8'}, list{'9'}, list{'10'}, list{'11'}, list{'12'}, list{'13'},
     *     list{'14'}, list{'15'}, list{'16'}, list{'17'}, list{'19'}, list{'20'}, list{'21'}, list{'191'},
     *     list{'192'}, list{'193'}, list{'194'}, list{'195'}, list{'196'}, list{'197'}, list{'198'}, list{'199'},
     *     list{'241'}, list{'242'}}
     */
    public static function provideInvalidLuhnNumbers(): array
    {
        return [
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
     * @throws ReflectionException
     */
    public function testIsValidLuhnChecksumHandlesStringDigitsAsIntegers(): void
    {
        // Use a number where not casting to int would fail the Luhn check due to string concatenation instead of arithmetic addition.
        $number = '4111111111111111';
        // A valid Visa credit card number
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertTrue($result, 'Failed to handle string digits as integers correctly.');
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


    /**
     * Provides a dataset of numbers with their expected Luhn validation outcome and a description.
     *
     * @return array<array{0: string, 1: bool, 2: string}>
     */
    public static function provideNumbersWithExpectedOutcome(): array
    {
        return [
            ['1234567812345670', true, 'Valid Luhn number with even digits'],
            // Invalid Luhn numbers
            ['79927398714', false, 'Classic invalid Luhn number'],
            ['1234567812345678', false, 'Invalid Luhn number with even digits'],
            // Edge cases and specific tests
            ['0', true, 'Minimum valid Luhn number'],
            ['18', true, 'Valid Luhn number, testing edge case'],
            ['79927398713', true, 'Testing PlusEqual mutation'],
            ['091', true, 'Testing ExactDoublingToNine mutation'],
            // Other specific cases
            ['123abc', false, 'Non-numeric string expected to fail'],
            ['4561231231234', false, 'Invalid number expected to fail'],
        ];
    }

    /**
     * @dataProvider provideNumbersWithExpectedOutcome
     *
     * @throws ReflectionException
     */
    public function testLuhnNumberValidation(string $number, bool $expectedOutcome, string $description): void
    {
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertSame(
            $expectedOutcome,
            $result,
            sprintf("Test case '%s' failed. Expected '%s'.", $description, $expectedOutcome ? 'true' : 'false')
        );
    }


}
