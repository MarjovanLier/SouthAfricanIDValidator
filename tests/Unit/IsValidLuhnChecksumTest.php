<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Tests the Luhn checksum validation method in the SouthAfricanIDValidator class.
 */
#[CoversMethod(SouthAfricanIDValidator::class, 'isValidLuhnChecksum')]
final class IsValidLuhnChecksumTest extends TestCase
{
    /**
     * Provides valid Luhn numbers for testing.
     *
     * @return array<array<string>>
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
     * Provides invalid Luhn numbers for testing.
     *
     * @return array<array<string>>
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
     * Provides numbers with their expected Luhn validation outcome and a description for
     * testing.
     *
     * @return (bool|string)[][]
     */
    public static function provideNumbersWithExpectedOutcome(): array
    {
        return [
            [
                '1234567812345670',
                true,
                'Valid Luhn number with even digits',
            ],
            // Invalid Luhn numbers
            [
                '79927398714',
                false,
                'Classic invalid Luhn number',
            ],
            [
                '1234567812345678',
                false,
                'Invalid Luhn number with even digits',
            ],
            // Edge cases and specific tests
            [
                '0',
                true,
                'Minimum valid Luhn number',
            ],
            [
                '18',
                true,
                'Valid Luhn number, testing edge case',
            ],
            [
                '79927398713',
                true,
                'Testing PlusEqual mutation',
            ],
            [
                '091',
                true,
                'Testing ExactDoublingToNine mutation',
            ],
            // Other specific cases
            [
                '123abc',
                false,
                'Non-numeric string expected to fail',
            ],
            [
                '4561231231234',
                false,
                'Invalid number expected to fail',
            ],
        ];
    }


    /**
     * Tests the Luhn validation method with valid Luhn numbers.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws ReflectionException
     */
    #[DataProvider('provideValidLuhnNumbers')]
    public function testValidLuhnNumbers(string $number): void
    {
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);

        self::assertTrue(
            $result,
            sprintf("Number '%s' must pass Luhn checksum validation as it is a valid Luhn number", $number),
        );
    }

    /**
     * Returns a ReflectionMethod instance of the private method 'isValidLuhnChecksum' in the
     * SouthAfricanIDValidator class.
     *
     * @throws ReflectionException
     */
    private function getPrivateMethod(): ReflectionMethod
    {
        /**
         * @noinspection PhpExpressionResultUnusedInspection
         */

        return (new ReflectionClass(SouthAfricanIDValidator::class))->getMethod('isValidLuhnChecksum');
    }

    /**
     * Tests the Luhn validation method with invalid Luhn numbers.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws ReflectionException
     */
    #[DataProvider('provideInvalidLuhnNumbers')]
    public function testInvalidLuhnNumbers(string $number): void
    {
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertFalse(
            $result,
            sprintf("Number '%s' must fail Luhn checksum validation as it is an invalid Luhn number", $number),
        );
    }

    /**
     * Tests the Luhn validation method with valid Luhn numbers and verifies integer casting.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws ReflectionException
     */
    #[DataProvider('provideValidLuhnNumbers')]
    public function testValidLuhnNumbersWithCastIntMutation(string $number): void
    {
        // Test to catch CastInt mutation
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertTrue(
            $result,
            sprintf("Number '%s' must be valid. Failure may indicate issues with integer casting.", $number),
        );
    }

    /**
     * Tests the Luhn validation method with a number where not casting to int would fail the Luhn check
     *      due to string concatenation instead of arithmetic addition.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws ReflectionException
     */
    public function testIsValidLuhnChecksumHandlesStringDigitsAsIntegers(): void
    {
        // Utilise a number where not casting to int would fail the Luhn check due to string concatenation instead of arithmetic addition.
        $number = '4111111111111111';
        // A valid Visa credit card number
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertTrue($result, 'Failed to handle string digits as integers correctly.');
    }

    /**
     * Tests the Luhn validation method with a dataset of numbers and their expected Luhn validation
     *      outcome.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws ReflectionException
     */
    #[DataProvider('provideNumbersWithExpectedOutcome')]
    public function testLuhnNumberValidation(string $number, bool $expectedOutcome, string $description): void
    {
        $result = $this->getPrivateMethod()->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertSame(
            $expectedOutcome,
            $result,
            sprintf("Test case '%s' failed. Expected '%s'.", $description, $expectedOutcome ? 'true' : 'false'),
        );
    }
}
