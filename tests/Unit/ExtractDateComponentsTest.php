<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the extractDateComponents method in SouthAfricanIDValidator.
 */
final class ExtractDateComponentsTest extends TestCase
{
    /**
     * Tests extractDateComponents with a valid ID.
     */
    public function testExtractDateComponentsWithValidId(): void
    {
        $idNumber = '8001015009087'; // Date: 80-01-01

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        $this->assertIsArray($result, 'Should return an array');
        $this->assertSame('80', $result['year'], 'Year should be 80');
        $this->assertSame('01', $result['month'], 'Month should be 01');
        $this->assertSame('01', $result['day'], 'Day should be 01');
    }

    /**
     * Tests extractDateComponents with different valid dates.
     */
    public function testExtractDateComponentsWithVariousDates(): void
    {
        $testCases = [
            '9912315009089' => ['year' => '99', 'month' => '12', 'day' => '31'], // End of year
            '0002295009084' => ['year' => '00', 'month' => '02', 'day' => '29'], // Leap year
            '5006155009086' => ['year' => '50', 'month' => '06', 'day' => '15'], // Mid-year
        ];

        foreach ($testCases as $idNumber => $expected) {
            $result = SouthAfricanIDValidator::extractDateComponents((string) $idNumber);

            $this->assertIsArray($result, sprintf('Should return array for ID %s', $idNumber));
            $this->assertSame($expected['year'], $result['year'], sprintf('Year mismatch for ID %s', $idNumber));
            $this->assertSame($expected['month'], $result['month'], sprintf('Month mismatch for ID %s', $idNumber));
            $this->assertSame($expected['day'], $result['day'], sprintf('Day mismatch for ID %s', $idNumber));
        }
    }

    /**
     * Tests extractDateComponents with invalid date (like 32nd day).
     */
    public function testExtractDateComponentsWithInvalidDate(): void
    {
        $idNumber = '8001325009089'; // January 32nd (invalid)

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        $this->assertNull($result, 'Should return null for invalid date');
    }

    /**
     * Tests extractDateComponents with invalid month.
     */
    public function testExtractDateComponentsWithInvalidMonth(): void
    {
        $idNumber = '8013015009081'; // Month 13 (invalid)

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        $this->assertNull($result, 'Should return null for invalid month');
    }

    /**
     * Tests extractDateComponents with February 29 in non-leap year.
     */
    public function testExtractDateComponentsWithInvalidLeapDay(): void
    {
        // 01 could be 1901 (not a leap year) or 2001 (not a leap year)
        // But could also be 1801 (not leap) or hypothetically 2101
        // The validation should check if it is valid in ANY possible century
        $idNumber = '0102295009088'; // Feb 29, 01

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        // This should be null as 01 represents years ending in 01,
        // which are not leap years in any century we check (1801, 1901, 2001)
        $this->assertNull($result, 'Should return null for Feb 29 in non-leap year');
    }

    /**
     * Tests extractDateComponents with February 29 in leap year.
     */
    public function testExtractDateComponentsWithValidLeapDay(): void
    {
        // 00 could be 1900 (not leap), 2000 (leap), or 1800 (not leap)
        // Should be valid because 2000 is a leap year
        $idNumber = '0002295009084'; // Feb 29, 00

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        $this->assertIsArray($result, 'Should return array for valid leap day');
        $this->assertSame('00', $result['year'], 'Year should be 00');
        $this->assertSame('02', $result['month'], 'Month should be 02');
        $this->assertSame('29', $result['day'], 'Day should be 29');
    }

    /**
     * Tests extractDateComponents with invalid ID length.
     */
    public function testExtractDateComponentsWithInvalidLength(): void
    {
        $idNumber = '800101'; // Too short

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        $this->assertNull($result, 'Should return null for invalid length');
    }

    /**
     * Tests extractDateComponents with non-numeric characters.
     */
    public function testExtractDateComponentsWithNonNumericCharacters(): void
    {
        $idNumber = '80-01-01 5009-087'; // Valid ID with formatting

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        $this->assertIsArray($result, 'Should handle formatted IDs');
        $this->assertSame('80', $result['year'], 'Year should be extracted correctly');
        $this->assertSame('01', $result['month'], 'Month should be extracted correctly');
        $this->assertSame('01', $result['day'], 'Day should be extracted correctly');
    }

    /**
     * Tests extractDateComponents preserves leading zeros.
     */
    public function testExtractDateComponentsPreservesLeadingZeros(): void
    {
        $idNumber = '0501055009088'; // Year 05, Month 01, Day 05

        $result = SouthAfricanIDValidator::extractDateComponents($idNumber);

        $this->assertIsArray($result, 'Should return array');
        $this->assertSame('05', $result['year'], 'Should preserve leading zero in year');
        $this->assertSame('01', $result['month'], 'Should preserve leading zero in month');
        $this->assertSame('05', $result['day'], 'Should preserve leading zero in day');
    }
}
