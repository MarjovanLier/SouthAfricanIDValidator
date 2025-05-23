<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for invalid date combinations in South African ID numbers.
 */
#[CoversMethod(SouthAfricanIDValidator::class, 'luhnIDValidate')]
final class InvalidDateTest extends TestCase
{
    /**
     * Provides ID numbers with invalid date combinations.
     *
     * @return array<array<string>>
     */
    public static function provideInvalidDateNumbers(): array
    {
        return [
            // Invalid months
            'month 00' => ['8700015800085'],
            'month 13' => ['8713015800085'],
            'month 20' => ['8720015800085'],
            'month 99' => ['8799015800085'],

            // Invalid days
            'day 00' => ['8701005800085'],
            'day 32' => ['8701325800085'],
            'day 40' => ['8701405800085'],
            'day 99' => ['8701995800085'],

            // Invalid month/day combinations
            'April 31' => ['8704315800085'],
            'June 31' => ['8706315800085'],
            'September 31' => ['8709315800085'],
            'November 31' => ['8711315800085'],
            'February 30' => ['8702305800085'],
            'February 31' => ['8702315800085'],
        ];
    }

    /**
     * Tests that invalid date combinations are rejected.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('provideInvalidDateNumbers')]
    public function testInvalidDates(string $idNumber): void
    {
        $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);
        self::assertFalse($result, 'Invalid date should be rejected: ' . $idNumber);
    }

    /**
     * Tests edge cases for valid dates.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testValidDateEdgeCases(): void
    {
        // These should be valid (using existing valid IDs as base)
        $validDates = [
            '8701105800085', // January 10
            '3202295029085', // February 29 (leap year check)
            '4806010046080', // June 1
            '3206015052087', // June 1
            '0809015019080', // September 1
        ];

        foreach ($validDates as $validDate) {
            self::assertTrue(
                SouthAfricanIDValidator::luhnIDValidate($validDate),
                'Valid date should be accepted: ' . $validDate,
            );
        }
    }

    /**
     * Tests all zero date.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testAllZeroDate(): void
    {
        $allZeroDate = '0000005800085';
        $result = SouthAfricanIDValidator::luhnIDValidate($allZeroDate);
        self::assertFalse($result, 'All zero date should be invalid');
    }

    /**
     * Tests boundary month values.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testBoundaryMonths(): void
    {
        // Test valid months 01-12 using a known valid ID pattern
        $baseId = '8701105800085';

        // Month 01 (January) - using existing valid ID
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($baseId),
            'January (01) should be valid',
        );

        // Month 12 would need a different checksum, so we test with modified dates
        // that we know are invalid
        $invalidMonth13 = str_replace('8701', '8713', $baseId);
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalidMonth13),
            'Month 13 should be invalid',
        );
    }

    /**
     * Tests specific invalid date patterns.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testSpecificInvalidPatterns(): void
    {
        // Using the pattern from existing valid IDs but with invalid dates
        // Replace valid date with invalid ones
        $patterns = [
            '9913315800085', // Invalid month 13
            '8732105800085', // Invalid day 32
            '8700105800085', // Invalid month 00
            '8701005800085', // Invalid day 00
        ];

        foreach ($patterns as $pattern) {
            $result = SouthAfricanIDValidator::luhnIDValidate($pattern);
            self::assertNotTrue($result, 'Invalid date pattern should not be valid: ' . $pattern);
        }
    }
}
