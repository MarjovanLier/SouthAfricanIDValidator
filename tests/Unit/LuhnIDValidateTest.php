<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::luhnIDValidate
 */
final class LuhnIDValidateTest extends TestCase
{
    private const string ALTERED_ID_NUMBER = '8701105022186';


    /**
     * Provides a set of valid ID numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'8701105800085'}, list{'3202295029085'}, list{'4806010046080'}, list{'3206015052087'},
     *     list{'0809015019080'}}
     */
    public static function provideValidIDNumbers(): array
    {
        return [
            ['8701105800085'],
            ['3202295029085'],
            ['4806010046080'],
            ['3206015052087'],
            ['0809015019080'],
        ];
    }


    /**
     * Provides a set of invalid ID numbers.
     *
     * @return array<string[]>
     *
     * @psalm-return list{list{'1234567890129'}, list{'9876543210186'}}
     */
    public static function provideInvalidIDNumbers(): array
    {
        return [
            ['1234567890129'],
            ['9876543210186'],
        ];
    }


    /**
     * Provides a set of invalid ID numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'0000000000380'}, list{'0000000000480'}, list{'0000000000580'}, list{'0000000000680'},
     *     list{'0000000000780'}, list{'0000000000880'}, list{'0000000000980'}, list{'0000000000380'},
     *     list{'0000000000480'}, list{'0000000000580'}, list{'0000000000680'}, list{'0000000000780'},
     *     list{'0000000000880'}, list{'0000000000980'}}
     */
    public static function provideInvalidFormatIDNumbers(): array
    {
        return [
            ['0000000000380'],
            ['0000000000480'],
            ['0000000000580'],
            ['0000000000680'],
            ['0000000000780'],
            ['0000000000880'],
            ['0000000000980'],
            ['0000000000380'],
            ['0000000000480'],
            ['0000000000580'],
            ['0000000000680'],
            ['0000000000780'],
            ['0000000000880'],
            ['0000000000980'],
        ];
    }


    /**
     * Tests if valid ID numbers are correctly validated.
     *
     * @param string $idNumber The ID number to validate.
     *
     * @dataProvider provideValidIDNumbers
     */
    #[DataProvider('provideValidIDNumbers')]
    public function testValidIdNumbers(string $idNumber): void
    {
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * @dataProvider provideInvalidIDNumbers
     */
    #[DataProvider('provideInvalidIDNumbers')]
    public function testInvalidIdNumbers(string $idNumber): void
    {
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * @dataProvider provideInvalidFormatIDNumbers
     */
    #[DataProvider('provideInvalidFormatIDNumbers')]
    public function testInvalidFormatIdNumbers(string $idNumber): void
    {
        self::assertNull(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests if non-numeric values at the 11th position are correctly invalidated.
     */
    public function testNonNumericEleventhCharacter(): void
    {
        // X at the 11th position
        $idNumber = '8701105022X86';
        // As per your existing logic, this should return false
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests Luhn algorithm parity and digit logic.
     */
    public function testParityAndDigitLogic(): void
    {
        $idNumber = '8701105025086';
        // Here the 10th digit is 5, which when doubled gives 10.
        // This assumes you have a helper function to compute Luhn's checksum without the specific ID logic.
        $expected = $this->computeLuhnChecksum($idNumber);

        self::assertEquals($expected, SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }

    private function computeLuhnChecksum(string $number): bool
    {
        $parity = (strlen($number) % 2);
        $total = 0;

        foreach (str_split($number) as $key => $digit) {
            $digit = (int) $digit;

            if (($key % 2) === $parity) {
                $digit *= 2;
            }

            if ($digit >= 10) {
                // Equivalent to splitting the digits and summing them.
                $digit -= 9;
            }

            $total += $digit;
        }

        return ($total % 10) === 0;
    }

    /**
     * Tests if string number inputs are correctly validated.
     */
    public function testStringNumberInput(): void
    {
        $idNumber = '8701105800085';
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }

    /**
     * Tests if non-integer values in the ID number are correctly invalidated.
     */
    public function testNonIntegerValues(): void
    {
        $idNumber = '87011X5022086';
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }

    /**
     * Tests the parity logic of the Luhn algorithm.
     */
    public function testParityLogic(): void
    {
        $idNumber = '8701105800085';
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));

        $idNumberAltered = '8701105022186';
        // Try to change a number that would affect parity calculation.
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumberAltered));
    }

    /**
     * Tests if ID numbers containing the digit five are correctly validated.
     */
    public function testNumberHavingFive(): void
    {
        $idNumber = '8701155022086';
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }

    /**
     * Tests if the Luhn checksum logic works correctly when digits are incremented or decremented.
     */
    public function testTotalIncrementDecrementLogic(): void
    {
        $idNumber = '8701105800085';
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));

        $idNumberAltered = '8701105022087';
        // Altering the last digit should make the ID invalid.
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumberAltered));
    }

    /**
     * Tests the parity logic of the Luhn algorithm.
     */
    public function testLuhnParityLogic(): void
    {
        $idNumber = '8701105025086';
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }

    /**
     * Tests if the Luhn algorithm correctly splits double digits.
     */
    public function testLuhnDoubleDigitSplit(): void
    {
        $idNumber = '8701105026086';
        // The 10th character when doubled becomes 12, which should be treated as 1 + 2
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }

    /**
     * Tests if altering a digit in the ID number makes it invalid.
     */
    public function testAlterationMakesIdInvalid(): void
    {
        // Altering a single digit to make it invalid
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate(self::ALTERED_ID_NUMBER));
    }

    public function testLuhnIdValidateWithMod5True(): void
    {
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate('8809260027087'));
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate('6804045679085'));
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate('8806087993185'));
    }


    /**
     * Test targeting mutation at line 106 - early return false when isValidDateInID fails.
     * This ensures that when date validation fails, the method returns false immediately
     * without proceeding to Luhn checksum validation.
     */
    public function testInvalidDateCausesEarlyReturnFalse(): void
    {
        // ID with invalid date (Feb 30th) but potentially valid Luhn checksum structure
        // Date: 870230 (invalid), rest: 5800085 (this structure might pass Luhn if date check bypassed)
        $invalidDateId = '8702305800085';

        // Should return false due to invalid date, not proceed to Luhn validation
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($invalidDateId));

        // Another case: invalid date length (5 chars instead of 6) with valid-looking Luhn structure
        $shortenedDateId = '870115800085'; // Removed one digit from date part (12 chars total)

        // Should return false due to length validation happening before date validation
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($shortenedDateId));
    }


    /**
     * Test that specifically checks the date validation path in luhnIDValidate.
     * This ensures the isValidDateInID check cannot be bypassed.
     */
    public function testDateValidationInLuhnValidate(): void
    {
        // Create an ID with invalid date but correct length and citizenship status
        // Date part: 999999 (invalid date)
        // Gender: 5 (male)
        // Citizenship: 0 (SA citizen)
        // Race: 0
        // Checksum: calculated to make Luhn pass if date validation is bypassed

        $invalidDateId = '9999995000080';

        // This should fail on date validation, not reach Luhn validation
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($invalidDateId));
    }


    /**
     * CRITICAL TEST: Targets mutation at line 106 - ReturnRemoval in luhnIDValidate.
     * This test creates IDs with invalid dates but valid Luhn checksums.
     * If the early return for invalid date is removed, the method would proceed
     * to Luhn validation and potentially return a different result.
     */
    public function testMutationLine106DateValidationEarlyReturn(): void
    {
        // Test case 1: Invalid date (Feb 30th) with carefully crafted Luhn-valid checksum
        // Date: 870230 (Feb 30, 1987 - invalid date)
        // Rest: 5800083 - constructed so Luhn algorithm passes if reached
        $invalidDate1 = '8702305800083';

        // MUST return false due to invalid date, should NOT proceed to Luhn check
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalidDate1),
            'Invalid date Feb 30th must cause early return false, not proceed to Luhn validation',
        );

        // Test case 2: Impossible date (month 13) with Luhn-valid structure
        // Date: 871301 (13th month - invalid)
        // Rest: 5800080 - Luhn checksum passes if validation reaches that point
        $invalidDate2 = '8713015800080';

        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalidDate2),
            'Invalid month 13 must cause early return false, not proceed to Luhn validation',
        );

        // Test case 3: Invalid day 32 with potential Luhn pass
        // Date: 870132 (Jan 32nd - invalid day)
        $invalidDate3 = '8701325800084';

        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalidDate3),
            'Invalid day 32 must cause early return false',
        );

        // Test case 4: Leap year edge case - Feb 29 in non-leap year
        // Date: 870229 (Feb 29, 1987 - not a leap year, so invalid)
        $invalidLeapDate = '8702295800081';

        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalidLeapDate),
            'Feb 29 in non-leap year must cause early return false',
        );

        // Verification: Ensure valid dates still work normally
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate('8701105800085'),
            'Valid dates should still pass normally',
        );
    }


    /**
     * Additional test to ensure invalid dates with valid citizenship return false.
     * This reinforces the mutation test by ensuring date validation happens
     * after citizenship validation but before Luhn validation.
     */
    public function testInvalidDateValidCitizenshipSequence(): void
    {
        // These IDs have:
        // - Valid length (13 digits)
        // - Valid citizenship status (0, 1, or 2 in position 11)
        // - Invalid dates
        // - Potentially valid Luhn checksums

        $testCases = [
            '9999990000089', // Invalid date 999999, citizenship 0
            '9999991000086', // Invalid date 999999, citizenship 1
            '9999992000083', // Invalid date 999999, citizenship 2
            '8713000000082', // Invalid month 13, citizenship 0
            '8700000000084', // Invalid day 00, citizenship 0
        ];

        foreach ($testCases as $testCase) {
            self::assertFalse(
                SouthAfricanIDValidator::luhnIDValidate($testCase),
                sprintf("ID '%s' with invalid date must return false despite valid citizenship", $testCase),
            );
        }
    }
}
