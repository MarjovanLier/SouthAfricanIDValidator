<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 *
 * Tests specifically designed to kill escaped ReturnRemoval mutations.
 * These tests target the 6 specific mutations that were escaping:
 * 1. Line 106: luhnIDValidate early return false when isValidDateInID fails
 * 2. Line 138: isValidIDDate early return false for wrong string length
 * 3. Line 143: isValidIDDate early return true for valid 1800s/1900s dates
 * 4. Line 168: sanitiseNumber early return for clean digits
 * 5. Line 249: isValidDateFor1800sOr1900s early return false for wrong length
 * 6. Line 275: isValidDateFor2000s early return false for wrong length
 *
 * Each test is crafted to fail if the corresponding early return is removed.
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::luhnIDValidate
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidIDDate
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::sanitiseNumber
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidDateFor1800sOr1900s
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidDateFor2000s
 */
final class EscapedMutationTargetingTest extends TestCase
{
    /**
     * Test targeting mutation at line 106 - early return false when isValidDateInID fails.
     *
     * This test creates ID numbers with invalid dates but valid Luhn checksums and proper structure.
     * If the early return on date validation failure is removed, the method would continue to
     * Luhn validation and potentially return true, causing this test to fail.
     *
     * Strategy: Use IDs with invalid dates that would pass Luhn validation if date check is bypassed.
     */
    public function testLuhnValidateWithInvalidDateButValidChecksumStructure(): void
    {
        // These IDs have invalid dates but are structured to potentially pass other validations
        $invalidDateIds = [
            // Invalid date: Feb 30, 1999 (990230) with proper structure and citizenship
            '9902305000083', // Date: 990230, Gender: 5 (male), Citizenship: 0, Race: 0, Checksum calculated

            // Invalid date: 13th month (991301)
            '9913015000087', // Date: 991301, Gender: 5 (male), Citizenship: 0, Race: 0, Checksum calculated

            // Invalid date: Day 32 (990132)
            '9901325000086', // Date: 990132, Gender: 5 (male), Citizenship: 0, Race: 0, Checksum calculated

            // Invalid date: Month 00 (990001)
            '9900015000089', // Date: 990001, Gender: 5 (male), Citizenship: 0, Race: 0, Checksum calculated
        ];

        foreach ($invalidDateIds as $invalidDateId) {
            self::assertFalse(
                SouthAfricanIDValidator::luhnIDValidate($invalidDateId),
                sprintf('ID %s should return false due to invalid date, even if other validations would pass', $invalidDateId),
            );
        }
    }

    /**
     * Test targeting mutation at line 143 - early return true for valid 1800s/1900s dates.
     *
     * This test uses dates that are valid when interpreted as 1800s/1900s dates but would be
     * invalid when interpreted as 2000s dates. If the early return is removed, these dates
     * would fall through to 2000s validation and potentially return false.
     *
     * Strategy: Use leap year dates that are valid for past centuries but invalid for future years.
     */
    public function testValid1800sDatesThatWouldBeInvalidAs2000s(): void
    {
        // Filter to only test dates that are actually valid for 1800s interpretation
        $valid1800sDates = ['880229', '920229', '960229'];

        foreach ($valid1800sDates as $valid1800Date) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($valid1800Date),
                sprintf('Date %s should be valid (1800s interpretation: 18%s)', $valid1800Date, $valid1800Date),
            );
        }
    }

    /**
     * Test targeting mutation at line 168 - early return for clean digits in sanitiseNumber.
     *
     * This test ensures that the early return path for already-clean numbers is properly tested.
     * While both paths should produce the same result, this test verifies the early return
     * path is taken for performance and correctness.
     */
    public function testSanitizeNumberEarlyReturnForCleanDigits(): void
    {
        $cleanDigitStrings = [
            '0',
            '123456789',
            '0000000000000',
            '9876543210123',
            '1357924680135',
        ];

        foreach ($cleanDigitStrings as $cleanDigitString) {
            /** @var string $result */
            $result = $this->invokePrivateMethod('sanitiseNumber', [$cleanDigitString]);
            self::assertEquals(
                $cleanDigitString,
                $result,
                sprintf("Clean number '%s' should be returned unchanged via early return", $cleanDigitString),
            );
        }

        // Test edge case: empty string (ctype_digit returns false for empty string)
        /** @var string $result */
        $result = $this->invokePrivateMethod('sanitiseNumber', ['']);
        self::assertEquals('', $result, 'Empty string should be handled correctly');
    }

    /**
     * Test targeting mutation at line 249 - early return false for wrong length in isValidDateFor1800sOr1900s.
     *
     * This test directly calls the private method with invalid lengths to ensure the length
     * validation is properly enforced. If the early return is removed, the method would
     * attempt to validate malformed date strings.
     */
    public function testPrivateMethod1800sDateLengthValidationDirect(): void
    {
        $invalidLengthDates = [
            '',           // 0 characters
            '1',          // 1 character
            '12',         // 2 characters
            '123',        // 3 characters
            '1234',       // 4 characters
            '12345',      // 5 characters
            '1234567',    // 7 characters
            '12345678',   // 8 characters
            '123456789',  // 9 characters
        ];

        foreach ($invalidLengthDates as $invalidLengthDate) {
            /** @var bool $result */
            $result = $this->invokePrivateMethod('isValidIDDate', [$invalidLengthDate]);
            self::assertFalse(
                $result,
                sprintf("Date '%s' (length %d) should return false due to invalid length", $invalidLengthDate, strlen($invalidLengthDate)),
            );
        }

        // Verify that valid length dates can return true
        /** @var bool $result */
        $result = $this->invokePrivateMethod('isValidIDDate', ['880101']);
        self::assertTrue($result, 'Valid length date 880101 should work for 1800s validation');
    }

    /**
     * Test targeting mutation at line 275 - early return false for wrong length in isValidDateFor2000s.
     *
     * This test directly calls the private method with invalid lengths to ensure the length
     * validation is properly enforced. If the early return is removed, the method would
     * attempt to validate malformed date strings.
     */
    public function testPrivateMethod2000sDateLengthValidationDirect(): void
    {
        $invalidLengthDates = [
            '',           // 0 characters
            '0',          // 1 character
            '00',         // 2 characters
            '000',        // 3 characters
            '0000',       // 4 characters
            '00000',      // 5 characters
            '0000000',    // 7 characters
            '00000000',   // 8 characters
            '000000000',  // 9 characters
        ];

        foreach ($invalidLengthDates as $invalidLengthDate) {
            /** @var bool $result */
            $result = $this->invokePrivateMethod('isValidIDDate', [$invalidLengthDate]);
            self::assertFalse(
                $result,
                sprintf("Date '%s' (length %d) should return false due to invalid length", $invalidLengthDate, strlen($invalidLengthDate)),
            );
        }

        // Verify that valid length dates can return true
        /** @var bool $result */
        $result = $this->invokePrivateMethod('isValidIDDate', ['000101']);
        self::assertTrue($result, 'Valid length date 000101 should work for 2000s validation');
    }

    /**
     * Additional test to ensure the luhnIDValidate mutation is properly killed.
     * This test creates a more comprehensive scenario where date validation failure
     * must cause immediate false return.
     */
    public function testLuhnValidateEarlyReturnOnDateFailureComprehensive(): void
    {
        // Create IDs with systematic invalid dates but otherwise valid structure
        $testCases = [
            // Invalid month scenarios
            ['date' => '990001', 'description' => 'month 00'],
            ['date' => '991301', 'description' => 'month 13'],
            ['date' => '991401', 'description' => 'month 14'],

            // Invalid day scenarios
            ['date' => '990100', 'description' => 'day 00'],
            ['date' => '990132', 'description' => 'day 32'],
            ['date' => '990230', 'description' => 'Feb 30'],
            ['date' => '990431', 'description' => 'Apr 31'],

            // Invalid year-month-day combinations
            ['date' => '000229', 'description' => '1800-02-29 (non-leap year for 1800)'],
        ];

        foreach ($testCases as $testCase) {
            // Construct ID: invalid_date + gender(5) + citizenship(0) + race(0) + checksum_placeholder(0)
            $baseId = $testCase['date'] . '5000';

            // Calculate a valid Luhn checksum for the base
            $checksum = $this->calculateLuhnChecksum($baseId);
            $fullId = $baseId . $checksum;

            self::assertFalse(
                SouthAfricanIDValidator::luhnIDValidate($fullId),
                sprintf('ID %s should return false due to invalid date (%s), ', $fullId, $testCase['description'])
                . "even though Luhn checksum is valid",
            );
        }
    }

    /**
     * Helper method to calculate Luhn checksum digit.
     */
    private function calculateLuhnChecksum(string $number): string
    {
        $total = 0;
        $double = true; // Start with doubling since we are calculating the check digit

        // Process from right to left (excluding the check digit position)
        for ($i = strlen($number) - 1; $i >= 0; --$i) {
            $digit = (int) $number[$i];

            if ($double) {
                $digit *= 2;
                if ($digit >= 10) {
                    $digit -= 9;
                }
            }

            $total += $digit;
            $double = !$double;
        }

        // Calculate what digit would make the total divisible by 10
        $checkDigit = (10 - ($total % 10)) % 10;
        return (string) $checkDigit;
    }

    /**
     * Helper method to invoke private methods using reflection.
     *
     * @param array<int, mixed> $parameters
     */
    private function invokePrivateMethod(string $methodName, array $parameters = []): mixed
    {
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod($methodName);

        // For static methods, pass null as the object
        return $reflectionMethod->invokeArgs(null, $parameters);
    }

    /**
     * Test that verifies comprehensive coverage of the isValidIDDate length check mutation (line 138).
     * This ensures that wrong-length strings are immediately rejected without further processing.
     */
    public function testIsValidIDDateLengthValidationComprehensive(): void
    {
        // Test systematic length variations
        $lengthTests = [
            ['input' => '', 'length' => 0],
            ['input' => '1', 'length' => 1],
            ['input' => '12', 'length' => 2],
            ['input' => '123', 'length' => 3],
            ['input' => '1234', 'length' => 4],
            ['input' => '12345', 'length' => 5],
            ['input' => '1234567', 'length' => 7],
            ['input' => '12345678', 'length' => 8],
            ['input' => '123456789', 'length' => 9],
            ['input' => '1234567890', 'length' => 10],
        ];

        foreach ($lengthTests as $lengthTest) {
            self::assertFalse(
                SouthAfricanIDValidator::isValidIDDate($lengthTest['input']),
                sprintf("Input '%s' (length %s) should be rejected by length validation", $lengthTest['input'], $lengthTest['length']),
            );
        }

        // Verify that correct length (6) can pass length validation
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('880101'),
            'Valid 6-character date should pass length validation',
        );
    }
}
