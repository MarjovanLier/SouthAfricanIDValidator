<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Feature;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive date boundary tests for South African ID validation.
 *
 * Date validation is the most complex logic in the validator, handling three
 * centuries (1800s, 1900s, 2000s) and all calendar edge cases. These tests
 * verify every month's maximum valid day, leap year handling, and boundary
 * conditions that are prone to off-by-one errors.
 */
#[CoversClass(SouthAfricanIDValidator::class)]
final class DateBoundaryValidationTest extends TestCase
{
    /**
     * Provides valid YYMMDD dates for all 12 months with their maximum valid day.
     *
     * @return array<string, array{date: string}>
     */
    public static function validMaxDayPerMonthProvider(): array
    {
        return [
            'January max day 31' => ['date' => '950131'],
            'February max day 28 (non-leap)' => ['date' => '950228'],
            'March max day 31' => ['date' => '950331'],
            'April max day 30' => ['date' => '950430'],
            'May max day 31' => ['date' => '950531'],
            'June max day 30' => ['date' => '950630'],
            'July max day 31' => ['date' => '950731'],
            'August max day 31' => ['date' => '950831'],
            'September max day 30' => ['date' => '950930'],
            'October max day 31' => ['date' => '951031'],
            'November max day 30' => ['date' => '951130'],
            'December max day 31' => ['date' => '951231'],
        ];
    }


    /**
     * Tests that all 12 months accept their maximum valid day.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('validMaxDayPerMonthProvider')]
    public function testValidMaxDayPerMonth(string $date): void
    {
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate($date),
            \sprintf('Date %s should be valid (max day for month)', $date),
        );
    }


    /**
     * Provides invalid YYMMDD dates where the day exceeds the month's maximum.
     *
     * @return array<string, array{date: string}>
     */
    public static function invalidDayExceedsMonthProvider(): array
    {
        return [
            'January day 32' => ['date' => '950132'],
            'February day 30 (non-leap)' => ['date' => '950230'],
            'March day 32' => ['date' => '950332'],
            'April day 31' => ['date' => '950431'],
            'May day 32' => ['date' => '950532'],
            'June day 31' => ['date' => '950631'],
            'July day 32' => ['date' => '950732'],
            'August day 32' => ['date' => '950832'],
            'September day 31' => ['date' => '950931'],
            'October day 32' => ['date' => '951032'],
            'November day 31' => ['date' => '951131'],
            'December day 32' => ['date' => '951232'],
        ];
    }


    /**
     * Tests that days exceeding each month's maximum are rejected.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('invalidDayExceedsMonthProvider')]
    public function testInvalidDayExceedsMonth(string $date): void
    {
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate($date),
            \sprintf('Date %s should be invalid (day exceeds max for month)', $date),
        );
    }


    /**
     * Tests leap year February 29 across different centuries.
     *
     * Year 00 is a leap year in 2000 but NOT in 1900. Since the validator
     * accepts a date if it is valid in any century, Feb 29 with year 00
     * should be valid (valid in 2000).
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testLeapYearFebruaryBoundaries(): void
    {
        // Year 00: leap in 1800? No. Leap in 1900? No. Leap in 2000? Yes.
        // So 000229 should be valid.
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('000229'),
            'Year 00 Feb 29 should be valid (2000 is a leap year)',
        );

        // Year 04: leap in all centuries (1804, 1904, 2004)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('040229'),
            'Year 04 Feb 29 should be valid (divisible by 4 is leap)',
        );

        // Year 01: not a leap year in any century
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('010229'),
            'Year 01 Feb 29 should be invalid (not a leap year)',
        );

        // February 28 is always valid for any year
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('010228'),
            'Year 01 Feb 28 should always be valid',
        );

        // February 30 is never valid
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('000230'),
            'Feb 30 should never be valid, even in leap years',
        );
    }


    /**
     * Tests invalid month boundaries.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testInvalidMonthBoundaries(): void
    {
        // Month 00 is invalid
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('950001'),
            'Month 00 should be invalid',
        );

        // Month 13 is invalid
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('951301'),
            'Month 13 should be invalid',
        );

        // Month 99 is invalid
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('959901'),
            'Month 99 should be invalid',
        );
    }


    /**
     * Tests day boundary conditions.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testDayBoundaries(): void
    {
        // Day 00 is invalid
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('950100'),
            'Day 00 should be invalid',
        );

        // Day 01 is always valid for any valid month
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('950101'),
            'Day 01 should be valid for January',
        );

        // Day 01 valid for all months
        for ($month = 1; $month <= 12; ++$month) {
            $date = \sprintf('95%02d01', $month);
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($date),
                \sprintf('Day 01 should be valid for month %02d', $month),
            );
        }
    }


    /**
     * Provides century boundary year cases.
     *
     * @return array<string, array{date: string, valid: bool, description: string}>
     */
    public static function centuryBoundaryProvider(): array
    {
        return [
            'year 00 Jan 01' => [
                'date' => '000101',
                'valid' => true,
                'description' => 'Year 00 is valid in 1800, 1900, and 2000',
            ],
            'year 50 Jun 15' => [
                'date' => '500615',
                'valid' => true,
                'description' => 'Year 50 mid-century should be valid',
            ],
            'year 99 Dec 31' => [
                'date' => '991231',
                'valid' => true,
                'description' => 'Year 99 end of year should be valid',
            ],
            'year 99 Jan 01' => [
                'date' => '990101',
                'valid' => true,
                'description' => 'Year 99 start of year should be valid',
            ],
        ];
    }


    /**
     * Tests century boundary years.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('centuryBoundaryProvider')]
    public function testCenturyBoundaries(string $date, bool $valid, string $description): void
    {
        self::assertSame(
            $valid,
            SouthAfricanIDValidator::isValidIDDate($date),
            $description,
        );
    }


    /**
     * Tests that date validation integrates correctly with full ID validation.
     *
     * Ensures that an ID with an invalid date fails luhnIDValidate even if
     * the Luhn checksum would otherwise be correct.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testDateValidationInFullIdContext(): void
    {
        // ID with invalid date (month 13): 9513015000080
        // Even if checksum were correct, the date should cause rejection
        $invalidDateId = '9513015000080';

        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalidDateId),
            'Full ID validation must reject IDs with invalid dates',
        );

        // ID with invalid day (Feb 30): 9502305000080
        $invalidDayId = '9502305000080';

        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalidDayId),
            'Full ID validation must reject Feb 30',
        );
    }


    /**
     * Tests date extraction returns null for IDs with invalid dates.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testDateExtractionForInvalidDates(): void
    {
        // 13-digit string with invalid date (month 00)
        $invalidMonth = '9500015000080';
        self::assertNull(
            SouthAfricanIDValidator::extractDateComponents($invalidMonth),
            'extractDateComponents should return null for month 00',
        );

        // 13-digit string with invalid date (day 00)
        $invalidDay = '9501005000080';
        self::assertNull(
            SouthAfricanIDValidator::extractDateComponents($invalidDay),
            'extractDateComponents should return null for day 00',
        );
    }


    /**
     * Tests that all first-of-month dates are valid for every month.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testFirstOfMonthAcrossAllMonths(): void
    {
        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        foreach ($months as $month) {
            // Use year 95 as representative non-edge-case
            $date = '95' . $month . '01';
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($date),
                \sprintf('First of month %s should be valid', $month),
            );
        }
    }


    /**
     * Tests non-digit and malformed date strings.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testMalformedDateStrings(): void
    {
        // Too short
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('95011'));

        // Too long
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('9501011'));

        // Empty string
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate(''));

        // Non-digit characters
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('95ab01'));

        // Spaces
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate(' 95011'));
    }
}
