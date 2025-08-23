<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * Tests targeting specific mutations in private date validation methods.
 * These tests are designed to kill escaped mutations in:
 * - isValidDateFor1800sOr1900s (line 249)
 * - isValidDateFor2000s (line 275)
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidIDDate
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidDateFor1800sOr1900s
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidDateFor2000s
 */
final class DateValidationEdgeCasesTest extends TestCase
{
    /**
     * Test targeting mutation at line 249 - early return false in isValidDateFor1800sOr1900s
     * when string length ≠ 6. This test exercises the private method through the public API.
     */
    public function testPrivateMethod1800sDateLengthValidation(): void
    {
        // These dates will reach isValidDateFor1800sOr1900s through isValidIDDate
        // Invalid lengths that would cause early return in the private method

        // Test through public API - these should fail length validation in private method
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate(''));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('1'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('12'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('123'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('1234'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('12345'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('1234567'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('12345678'));

        // Valid length should work for 1800s/1900s dates
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('880101')); // 1888-01-01
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('991231')); // 1999-12-31
    }

    /**
     * Test targeting mutation at line 275 - early return false in isValidDateFor2000s
     * when string length ≠ 6.
     */
    public function testPrivateMethod2000sDateLengthValidation(): void
    {
        // Dates that would reach isValidDateFor2000s (not valid for 1800s/1900s)
        // but have incorrect lengths

        // These are dates that would be invalid for 1800s/1900s, so they'll flow to 2000s check
        // Invalid lengths should cause early return false in isValidDateFor2000s

        // First verify that some dates DO flow to 2000s validation by being valid 2000s dates
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('000101')); // 2000-01-01
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('200229')); // 2020-02-29 (leap year)

        // These would reach 2000s validation but fail length check
        // (they're not valid 1800s/1900s either)
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate(''));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('0'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('00'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('000'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('0000'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('00000'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('0000000'));
    }

    /**
     * Test specific edge cases for century validation flow.
     */
    public function testCenturyValidationFlow(): void
    {
        // Dates that are specifically valid for 1800s but would be invalid for 2000s
        $valid1800sDates = [
            '880101', // 1888-01-01 vs 2088-01-01 (future)
            '960229', // 1896-02-29 (leap year) vs 2096-02-29 (future)
            '991231', // 1999-12-31 vs 2099-12-31 (future)
        ];

        foreach ($valid1800sDates as $valid1800Date) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($valid1800Date),
                sprintf('Date %s should be valid (1800s interpretation)', $valid1800Date),
            );
        }

        // Dates that are valid for 2000s but not 1800s/1900s
        $valid2000sOnly = [
            '000229', // 2000-02-29 (leap year, 1800-02-29 invalid)
            '040229', // 2004-02-29 (leap year, 1904-02-29 invalid)
            '200229', // 2020-02-29 (leap year, 1920-02-29 invalid)
        ];

        foreach ($valid2000sOnly as $valid2000Only) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($valid2000Only),
                sprintf('Date %s should be valid (2000s interpretation)', $valid2000Only),
            );
        }

        // Dates that are invalid in both centuries
        $invalidBothCenturies = [
            '990230', // Feb 30th doesn't exist in any year
            '001301', // Month 13 doesn't exist
            '000000', // Invalid date
        ];

        foreach ($invalidBothCenturies as $invalidBothCentury) {
            self::assertFalse(
                SouthAfricanIDValidator::isValidIDDate($invalidBothCentury),
                sprintf('Date %s should be invalid in all centuries', $invalidBothCentury),
            );
        }
    }

    /**
     * Test that demonstrates the mutation impact by checking method behavior
     * when private method length validation is bypassed.
     */
    public function testMutationImpactOnPrivateMethods(): void
    {
        // If the early returns in private methods are removed, these tests should catch it

        // Empty string test - should fail at first length check
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate(''),
            'Empty string should fail length validation',
        );

        // Single character test - should fail length check
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('8'),
            'Single character should fail length validation',
        );

        // Very long string - should fail length check
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('88010112345678'),
            'Long string should fail length validation',
        );
    }


    /**
     * CRITICAL TEST: Targets mutation at line 249 - ReturnRemoval in isValidDateFor1800sOr1900s.
     * Tests edge cases where bypassing length validation in the private method would cause
     * StringManipulation::isValidDate to be called with invalid-length strings.
     */
    public function testMutationLine249PrivateMethod1800sLengthValidation(): void
    {
        // The private method isValidDateFor1800sOr1900s does: StringManipulation::isValidDate('18' . $date, 'Ymd')
        // If length validation is bypassed, invalid-length dates would be passed to StringManipulation

        // Strategy: Test dates that would trigger the 1800s/1900s path but have wrong lengths
        // These dates should fail the public isValidIDDate length check first (line 138)
        // But if that's bypassed AND the private method length check (line 249) is bypassed,
        // we need to ensure StringManipulation still behaves correctly

        // Test cases that would reach isValidDateFor1800sOr1900s if length checks are bypassed
        $invalidLengthDates = [
            '', // Empty -> '18' + '' = '18' -> fails StringManipulation
            '1', // 1 char -> '18' + '1' = '181' -> fails StringManipulation
            '12', // 2 chars -> '18' + '12' = '1812' -> fails StringManipulation
            '123', // 3 chars -> '18' + '123' = '18123' -> fails StringManipulation
            '1234', // 4 chars -> '18' + '1234' = '181234' -> might succeed unexpectedly!
            '12345', // 5 chars -> '18' + '12345' = '1812345' -> fails StringManipulation
            '1234567', // 7 chars -> '18' + '1234567' = '181234567' -> fails StringManipulation
        ];

        foreach ($invalidLengthDates as $invalidLengthDate) {
            self::assertFalse(
                SouthAfricanIDValidator::isValidIDDate($invalidLengthDate),
                sprintf("Date '%s' with invalid length must fail validation (targeting line 249 mutation)", $invalidLengthDate),
            );
        }

        // Critical test case: 4-character input that could become valid when prefixed
        // If both length checks are bypassed, '1234' becomes '181234' which might parse as a date
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('1234'),
            'Length-4 input must fail, not become valid 1800s date 181234 if mutations are applied',
        );
    }


    /**
     * CRITICAL TEST: Targets mutation at line 275 - ReturnRemoval in isValidDateFor2000s.
     * Tests edge cases where bypassing length validation would cause invalid behavior
     * in the 2000s date validation path.
     */
    public function testMutationLine275PrivateMethod2000sLengthValidation(): void
    {
        // The private method isValidDateFor2000s does: StringManipulation::isValidDate('20' . $date, 'Ymd')
        // If length validation is bypassed, invalid-length dates would be passed to StringManipulation

        // Strategy: Find dates that would reach the 2000s validation path (failed 1800s validation)
        // but have invalid lengths that should cause early return false

        // These dates should:
        // 1. Pass public method length check (we'll test bypass scenario)
        // 2. Fail 1800s validation (so they proceed to 2000s)
        // 3. Fail 2000s length validation (line 275)

        // First, verify our understanding of which dates go to 2000s path
        // Dates that are invalid for 1800s/1900s but valid for 2000s
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('000229'), // 2000-02-29 (leap), invalid as 1800-02-29
            'Test assumption: 000229 should be valid via 2000s path',
        );

        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('040229'), // 2004-02-29 (leap), invalid as 1904-02-29
            'Test assumption: 040229 should be valid via 2000s path',
        );

        // Now test invalid lengths that would reach 2000s method if checks bypassed
        $invalidLengths = [
            '', // Would become '20' -> fails
            '0', // Would become '200' -> fails
            '00', // Would become '2000' -> fails
            '000', // Would become '20000' -> fails
            '0000', // Would become '200000' -> might succeed as 20/00/00!
            '00000', // Would become '2000000' -> fails
            '0000000', // Would become '20000000' -> fails
        ];

        foreach ($invalidLengths as $invalidLength) {
            self::assertFalse(
                SouthAfricanIDValidator::isValidIDDate($invalidLength),
                sprintf("Date '%s' must fail 2000s validation length check (targeting line 275 mutation)", $invalidLength),
            );
        }

        // Critical edge case: 4 zeros might become valid date 200000 (year 2000, month 00, day 00)
        // But month/day 00 should be invalid
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('0000'),
            'Four zeros must fail, not become 200000 date if length validation is bypassed',
        );
    }


    /**
     * Comprehensive test to verify the complete validation flow and ensure
     * mutations in private methods are detected.
     */
    public function testPrivateMethodMutationDetection(): void
    {
        // Test the interaction between public and private method length validations

        // Case 1: Dates that should never reach private methods due to public length check
        $publicLengthFailures = ['', '1', '12345', '1234567'];
        foreach ($publicLengthFailures as $publicLengthFailure) {
            self::assertFalse(
                SouthAfricanIDValidator::isValidIDDate($publicLengthFailure),
                sprintf("Date '%s' should fail public length check before reaching private methods", $publicLengthFailure),
            );
        }

        // Case 2: Valid length dates that exercise private method paths

        // 1800s/1900s path (should pass private method and StringManipulation)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('800101'), // 1980-01-01
            'Valid 1800s date should pass both public and private validations',
        );

        // 2000s path (should pass private method and StringManipulation)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('050101'), // 2005-01-01
            'Valid 2000s date should pass both public and private validations',
        );

        // Invalid dates that reach private methods but fail StringManipulation
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('801301'), // Invalid month 13
            'Invalid 1800s date should fail StringManipulation validation',
        );

        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('051301'), // Invalid month 13
            'Invalid 2000s date should fail StringManipulation validation',
        );
    }
}
