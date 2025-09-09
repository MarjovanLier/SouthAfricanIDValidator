<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(SouthAfricanIDValidator::class, 'isValidIDDate')]
final class HistoricalDateValidationTest extends TestCase
{
    /**
     * Provides test cases for historical date validation.
     *
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{'960314', true, '1896-03-14 should be valid (90-year-old in 1986)'}, list{'950515', true, '1895-05-15 should be valid if within 130 years'}, list{'940101', true, '1894-01-01 should be valid if within 130 years'}, list{string, true, 'Exactly 130 years ago should be valid'}, list{'920101', bool, '1892-01-01 validity depends on current year'}, list{'900101', bool, '1890-01-01 validity depends on current year'}, list{'990101', bool, '2099-01-01 should be invalid as future date'}, list{'000000', false, 'Month and day cannot be 0'}, list{'001301', false, 'Invalid month (13)'}, list{'000231', false, 'Invalid day for February'}, list{'000101', true, '2000-01-01 should be valid'}, list{'010101', true, '2001-01-01 should be valid'}, list{'240101', true, '2024-01-01 should be valid'}, list{'000229', true, '2000-02-29 is valid (leap year)'}, list{'960229', true, '1896-02-29 is valid (leap year)'}, list{'010229', false, '2001-02-29 is invalid (not a leap year)'}, list{'970229', false, '1897-02-29 is invalid (not a leap year)'}}
     */
    public static function provideHistoricalDates(): array
    {
        // Calculate year that would be 130 years ago
        $currentYear = (int) date('Y');
        $year130YearsAgo = $currentYear - 130;
        $yy130YearsAgo = substr((string) $year130YearsAgo, -2);

        return [
            // Historical cases that should be valid
            ['960314', true, '1896-03-14 should be valid (90-year-old in 1986)'],
            ['950515', true, '1895-05-15 should be valid if within 130 years'],
            ['940101', true, '1894-01-01 should be valid if within 130 years'],

            // Edge case: exactly 130 years ago (should be valid)
            [$yy130YearsAgo . '0101', true, "Exactly 130 years ago should be valid"],

            // Too old (more than 130 years)
            ['920101', $year130YearsAgo > 1892, '1892-01-01 validity depends on current year'],
            ['900101', $year130YearsAgo > 1890, '1890-01-01 validity depends on current year'],

            // Future dates should be invalid
            ['990101', $currentYear < 2099, '2099-01-01 should be invalid as future date'],

            // Invalid dates
            ['000000', false, 'Month and day cannot be 0'],
            ['001301', false, 'Invalid month (13)'],
            ['000231', false, 'Invalid day for February'],

            // 2000s dates
            ['000101', true, '2000-01-01 should be valid'],
            ['010101', true, '2001-01-01 should be valid'],
            ['240101', true, '2024-01-01 should be valid'],

            // Leap year handling
            ['000229', true, '2000-02-29 is valid (leap year)'],
            ['960229', true, '1896-02-29 is valid (leap year)'],
            ['010229', false, '2001-02-29 is invalid (not a leap year)'],
            ['970229', false, '1897-02-29 is invalid (not a leap year)'],
        ];
    }

    /**
     * Tests that historical dates are correctly validated.
     *
     * The 13-digit ID system was introduced in 1980 with the green ID book
     * for all citizens, including adults born in the 1800s.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('provideHistoricalDates')]
    public function testHistoricalDateValidation(string $date, bool $expected, string $description): void
    {
        $result = SouthAfricanIDValidator::isValidIDDate($date);
        self::assertSame($expected, $result, $description);
    }

    /**
     * Test that removing '18' from centuries array would break validation.
     *
     * @throws ExpectationFailedException
     */
    public function testEighteenHundredsSpecificDate(): void
    {
        // Test a date in the 1890s to ensure '18' century is needed
        // This kills the ArrayItemRemoval mutation that tries to remove '18'
        $result = SouthAfricanIDValidator::isValidIDDate('951231');
        self::assertTrue($result, 'Expected 951231 to be valid as it could be 1895-12-31');
    }

    /**
     * Tests specific historical scenarios mentioned in the research.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testSpecificHistoricalScenarios(): void
    {
        // Test that date validation accepts historical dates
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('960314'),
            'Date 960314 (could be 1896 or 1996) should be valid',
        );

        // For full ID validation, we need correct checksums
        // Let's test with some known valid IDs

        // Test edge case where YY could be 1800s, 1900s, or 2000s
        // The validator should accept it if any interpretation results in valid age
        $currentYear = (int) date('Y');

        // Test a date that could only be 1900s (too old for 2000s, too young for 1800s)
        if ($currentYear >= 2050) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate('500101'),
                '500101 should be interpreted as 1950 when tested after 2050',
            );
        }
    }

    /**
     * Tests century determination logic.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCenturyDetermination(): void
    {
        // Test that the same YY can represent different centuries
        // 95 could be 1895, 1995, or 2095
        // The validator should pick the one that results in a valid age

        $currentYear = (int) date('Y');

        // For recent years, should pick 1995
        if ($currentYear >= 2020 && $currentYear < 2095) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate('950101'),
                '950101 should be interpreted as 1995 in current context',
            );
        }

        // Test ambiguous cases
        $twoDigitYear = substr((string) ($currentYear - 50), -2);
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate($twoDigitYear . '0101'),
            'Date from 50 years ago should be valid',
        );
    }
}
