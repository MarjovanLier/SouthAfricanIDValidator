<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use DateTime;
use InvalidArgumentException;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive edge case testing for South African ID validation
 * Tests all boundary conditions and edge cases discovered through analysis
 */
final class ComprehensiveEdgeCaseTest extends TestCase
{
    /**
     * Test century boundary cases where YY could represent multiple centuries
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCenturyBoundaryValidation(): void
    {
        // Year 99 - could be 1899 or 1999
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('991231500000')),
            'ID with year 99 on 31 December must validate (could be 1899 or 1999)',
        );
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('990101500000')),
            'ID with year 99 on 1 January must validate (could be 1899 or 1999)',
        );

        // Year 00 - could be 1900 or 2000
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('000101000000')),
            'ID with year 00 on 1 January must validate (could be 1900 or 2000)',
        );
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('001231000000')),
            'ID with year 00 on 31 December must validate (could be 1900 or 2000)',
        );

        // Current year boundary
        $currentYear = date('y');
        $id1 = $this->generateValidId($currentYear . '0101500000');
        $id2 = $this->generateValidId($currentYear . '1231500000');
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($id1),
            sprintf('ID with current year %s on 1 January must validate', $currentYear),
        );
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($id2),
            sprintf('ID with current year %s on 31 December must validate', $currentYear),
        );
    }

    /**
     * Helper method to generate a valid ID with correct Luhn checksum
     *
     * @throws \InvalidArgumentException
     */
    private function generateValidId(string $prefix): string
    {
        // Ensure prefix is exactly 12 digits
        if (strlen($prefix) !== 12) {
            throw new InvalidArgumentException('Prefix must be exactly 12 digits');
        }

        $sum = 0;
        $double = true;

        for ($i = 11; $i >= 0; $i--) {
            $digit = (int) $prefix[$i];

            if ($double) {
                $digit *= 2;
                if ($digit >= 10) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double;
        }

        $checksum = (10 - ($sum % 10)) % 10;
        return $prefix . (string) $checksum;
    }

    /**
     * Test leap year handling including century leap year rules
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testLeapYearValidation(): void
    {
        // 2000 was a leap year (divisible by 400)
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('000229500000')),
            'ID with 29 February 2000 must validate (leap year divisible by 400)',
        );

        // Regular leap years
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('960229500000')),
            'ID with 29 February 1996 must validate (regular leap year)',
        );
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('040229500000')),
            'ID with 29 February 2004 must validate (regular leap year)',
        );
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('200229500000')),
            'ID with 29 February 2020 must validate (regular leap year)',
        );

        // Non-leap years should fail on Feb 29
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('010229500000')),
            'ID with 29 February 2001 must fail validation (not a leap year)',
        );
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('030229500000')),
            'ID with 29 February 2003 must fail validation (not a leap year)',
        );
    }

    /**
     * Test gender code boundaries
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGenderBoundaries(): void
    {
        // Female range: 0000-4999
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101000000')),
            'ID with minimum female gender code (0000) must be recognised as valid',
        ); // Min female
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101499900')),
            'ID with maximum female gender code (4999) must be recognised as valid',
        ); // Max female

        // Male range: 5000-9999
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101500000')),
            'ID with minimum male gender code (5000) must be recognised as valid',
        ); // Min male
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101999900')),
            'ID with maximum male gender code (9999) must be recognised as valid',
        ); // Max male
    }

    /**
     * Test all valid citizenship values
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCitizenshipValues(): void
    {
        // Valid values: 0, 1, 2
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101500000')),
            'ID with citizenship digit 0 (South African citizen) must be recognised as valid',
        ); // Citizen (0)
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101500010')),
            'ID with citizenship digit 1 (permanent resident) must be recognised as valid',
        ); // Permanent resident (1)
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101500020')),
            'ID with citizenship digit 2 (refugee) must be recognised as valid',
        ); // Refugee (2)

        // Invalid values: 3-9
        self::assertNull(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101500030')),
            'ID with invalid citizenship digit 3 must return null to indicate constraint violation',
        ); // Invalid (3)
        self::assertNull(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900101500040')),
            'ID with invalid citizenship digit 4 must return null to indicate constraint violation',
        ); // Invalid (4)
    }

    /**
     * Test all race indicator values (0-9 are all valid)
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testRaceIndicatorValues(): void
    {
        // Test all race indicators 0-9
        for ($race = 0; $race <= 9; $race++) {
            $idNumber = $this->generateValidId('90010150000' . (string) $race);
            self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber), sprintf('Failed for race indicator: %d, ID: %s', $race, $idNumber));
        }
    }

    /**
     * Test month boundary dates
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testMonthBoundaryDates(): void
    {
        // 31-day months
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900131500000')),
            'ID with 31 January must be recognised as valid date',
        ); // Jan 31
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900331500000')),
            'ID with 31 March must be recognised as valid date',
        ); // Mar 31
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900531500000')),
            'ID with 31 May must be recognised as valid date',
        ); // May 31
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900731500000')),
            'ID with 31 July must be recognised as valid date',
        ); // Jul 31
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900831500000')),
            'ID with 31 August must be recognised as valid date',
        ); // Aug 31
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('901031500000')),
            'ID with 31 October must be recognised as valid date',
        ); // Oct 31
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('901231500000')),
            'ID with 31 December must be recognised as valid date',
        ); // Dec 31

        // 30-day months
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900430500000')),
            'ID with 30 April must be recognised as valid date',
        ); // Apr 30
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900630500000')),
            'ID with 30 June must be recognised as valid date',
        ); // Jun 30
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900930500000')),
            'ID with 30 September must be recognised as valid date',
        ); // Sep 30
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('901130500000')),
            'ID with 30 November must be recognised as valid date',
        ); // Nov 30

        // Invalid dates
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900431500000')),
            'ID with 31 April must fail validation as April only has 30 days',
        ); // Apr 31
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900631500000')),
            'ID with 31 June must fail validation as June only has 30 days',
        ); // Jun 31
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900931500000')),
            'ID with 31 September must fail validation as September only has 30 days',
        ); // Sep 31
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('901131500000')),
            'ID with 31 November must fail validation as November only has 30 days',
        ); // Nov 31
    }

    /**
     * Test historical dates that could be from 1800s
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testHistoricalDates(): void
    {
        // IDs that could be from 1800s (based on age validation)
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('950524500000')),
            'ID with year 95 must be recognised as valid (could represent 1895 or 1995)',
        ); // Could be 1895
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('960101500000')),
            'ID with year 96 must be recognised as valid (could represent 1896 or 1996)',
        ); // Could be 1896 or 1996
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('970101500000')),
            'ID with year 97 must be recognised as valid (could represent 1897 or 1997)',
        ); // Could be 1897 or 1997
    }

    /**
     * Test input sanitization
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testInputSanitization(): void
    {
        // ID with spaces and hyphens should be sanitised
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate('800101-5009 087'),
            'ID with hyphens and spaces must be sanitised and recognised as valid',
        );
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate('8001015009087'),
            'Same ID without formatting must be recognised as valid',
        );

        // Same ID with different formatting
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate('80 01 01 5009 087'),
            'ID with spaces between groups must be sanitised and recognised as valid',
        );
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate('80-01-01-5009-087'),
            'ID with hyphens between all groups must be sanitised and recognised as valid',
        );
    }

    /**
     * Test invalid date components
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvalidDateComponents(): void
    {
        // Invalid months
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900001500000')),
            'ID with month 00 must fail validation as months range from 01 to 12',
        ); // Month 00
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('901301500000')),
            'ID with month 13 must fail validation as months range from 01 to 12',
        ); // Month 13

        // Invalid days
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900100500000')),
            'ID with day 00 must fail validation as days start from 01',
        ); // Day 00
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('900132500000')),
            'ID with day 32 must fail validation as no month has 32 days',
        ); // Day 32
    }

    /**
     * Test length validation
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testLengthValidation(): void
    {
        // Too short
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate('800101500908'),
            'ID with 12 digits must fail validation as South African IDs require exactly 13 digits',
        );

        // Too long
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate('80010150090877'),
            'ID with 14 digits must fail validation as South African IDs require exactly 13 digits',
        );

        // Correct length
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate('8001015009087'),
            'ID with exactly 13 digits must be recognised as having the correct length',
        );
    }

    /**
     * Test Luhn checksum edge cases
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testLuhnChecksumEdgeCases(): void
    {
        // ID with checksum digit 0
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($this->generateValidId('800101500000')),
            'ID with checksum digit 0 must be recognised as valid when Luhn algorithm produces 0',
        );

        // Test invalid checksums - intentionally use wrong checksums
        $validId = $this->generateValidId('800101500908');
        $lastDigit = (int) substr($validId, -1);
        $wrongChecksum1 = ($lastDigit + 1) % 10;
        $wrongChecksum2 = ($lastDigit + 2) % 10;

        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate(substr($validId, 0, 12) . (string) $wrongChecksum1),
            sprintf('ID with incorrect checksum digit %d must fail Luhn validation', $wrongChecksum1),
        );
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate(substr($validId, 0, 12) . (string) $wrongChecksum2),
            sprintf('ID with incorrect checksum digit %d must fail Luhn validation', $wrongChecksum2),
        );
    }

    /**
     * Test future date handling
     *
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testFutureDateHandling(): void
    {
        // Generate a future date ID
        $tomorrow = new DateTime('tomorrow');
        $year = $tomorrow->format('y');
        $month = $tomorrow->format('m');
        $day = $tomorrow->format('d');

        $futureId = $this->generateValidId($year . $month . $day . '500000');

        // Future dates might be interpreted as past dates from another century
        // So we cannot definitively say they should fail
        $result = SouthAfricanIDValidator::luhnIDValidate($futureId);
        self::assertIsBool(
            $result,
            sprintf('ID with future date %s must return boolean result, not null, as it could be interpreted as a past century date', $year . $month . $day),
        ); // Should return true or false, not null
    }
}
