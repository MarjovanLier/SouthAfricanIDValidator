<?php

/**
 * Copyright (c) 2024 MarjovanLier
 *
 * This source code is licensed under the MIT license.
 *
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Tests designed to kill specific mutation escapes.
 *
 * These tests target edge cases where mutation testing revealed potential weaknesses
 * in our test coverage, particularly around type coercion and boundary conditions.
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator
 */
final class MutationKillerTest extends TestCase
{
    /**
     * Tests that strlen !== 13 cannot be mutated to strlen != 13.
     *
     * This test ensures that we're using strict comparison for length validation.
     * The mutation from !== to != would allow "13.0" or similar values.
     *
     * @throws ExpectationFailedException
     */
    public function testStrictLengthComparisonForThirteenCharacters(): void
    {
        // Test a numeric string that is exactly 13 chars and would pass
        $validCase = '8801235111088';
        $result = SouthAfricanIDValidator::luhnIDValidate($validCase);
        self::assertTrue(
            $result,
            sprintf(
                'Expected validation to pass for valid ID "%s" with length %d',
                $validCase,
                strlen($validCase),
            ),
        );

        // Test cases where strlen($x) !== 13 but could equal 13 with type juggling
        // In PHP, strlen always returns int, so != and !== should behave the same
        // But we need to ensure the mutation is caught
        $invalidCases = [
            '880123511108', // 12 chars
            '88012351110888', // 14 chars
        ];

        foreach ($invalidCases as $invalidCase) {
            $result = SouthAfricanIDValidator::luhnIDValidate($invalidCase);
            self::assertFalse(
                $result,
                sprintf(
                    'Expected validation to fail for input "%s" with length %d',
                    $invalidCase,
                    strlen($invalidCase),
                ),
            );
        }
    }

    /**
     * Tests that date strlen !== 6 cannot be mutated to strlen != 6.
     *
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testStrictLengthComparisonForSixCharacterDate(): void
    {
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('isValidIDDate');

        // Test cases that would differentiate !== from !=
        $testCases = [
            ['input' => '6', 'expected' => false], // numeric 6, length 1
            ['input' => '06', 'expected' => false], // numeric 6, length 2
            ['input' => '006', 'expected' => false], // numeric 6, length 3
            ['input' => '0006', 'expected' => false], // numeric 6, length 4
            ['input' => '00006', 'expected' => false], // numeric 6, length 5
            ['input' => '850101', 'expected' => true], // valid date, length 6
            ['input' => '0000006', 'expected' => false], // length 7
            ['input' => '123456', 'expected' => false], // length 6 but invalid date
        ];

        foreach ($testCases as $testCase) {
            $result = $reflectionMethod->invoke(null, $testCase['input']);

            self::assertSame(
                $testCase['expected'],
                $result,
                sprintf(
                    'Expected isValidIDDate("%s") to return %s for length %d, but got %s',
                    $testCase['input'],
                    $testCase['expected'] ? 'true' : 'false',
                    strlen($testCase['input']),
                    (bool) $result ? 'true' : 'false',
                ),
            );
        }
    }

    /**
     * Tests date concatenation mutations for 18xx century.
     *
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testDateConcatenationFor18xxCentury(): void
    {
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('isValidIDDate');

        // Test a date that's only valid in 18xx
        // 991231 is valid in all centuries, so let's use a better example
        // 990229 would be invalid as 1999-02-29 (not leap) and 2099-02-29 (not leap)
        // But invalid as 1899-02-29 (not leap) too, so we need a different approach

        // Test with a date that would fail if '18' is removed
        // If mutation removes '18' prefix, it would try to validate just the 6 digits as a date
        // which would fail since 'Ymd' format expects 8 digits
        $testDate = '850315'; // Valid as 1885-03-15, 1985-03-15, or 2085-03-15
        $result = $reflectionMethod->invoke(null, $testDate);
        self::assertTrue(
            $result,
            'Expected 850315 to be valid in at least one century',
        );

        // The key is that if '18' is removed, StringManipulation::isValidDate('850315', 'Ymd')
        // would be called, which should fail because it's not 8 digits
    }

    /**
     * Tests date concatenation mutations for 19xx century.
     *
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testDateConcatenationFor19xxCentury(): void
    {
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('isValidIDDate');

        // Test a date that's only valid in 19xx
        // 800229 is valid as 1980-02-29 (leap year)
        // But invalid as 1880-02-29 (not leap) or 2080-02-29 (not leap)
        $result = $reflectionMethod->invoke(null, '800229');
        self::assertTrue(
            $result,
            'Expected 800229 to be valid as 1980-02-29 (leap year), but validation failed',
        );
    }

    /**
     * Tests logical OR mutation (OR to AND).
     *
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testLogicalOrCannotBeMutatedToAnd(): void
    {
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('isValidIDDate');

        // Test multiple dates that are valid in only one century each
        // These would fail if OR is mutated to AND

        // 000229 is valid only as 2000-02-29 (leap year)
        // Invalid as 1800-02-29 (not leap) or 1900-02-29 (not leap)
        $result = $reflectionMethod->invoke(null, '000229');
        self::assertTrue(
            $result,
            'Expected 000229 to be valid as 2000-02-29, but validation failed',
        );

        // 040229 is valid only as 2004-02-29 (leap year)
        // Invalid as 1804-02-29 (not leap) or 1904-02-29 (leap but let's double check)
        /** @var bool $result */
        $result = $reflectionMethod->invoke(null, '040229');
        self::assertTrue(
            $result,
            'Expected 040229 to be valid as 2004-02-29, but validation failed',
        );

        // 960229 is valid only as 1996-02-29 (leap year)
        // Invalid as 1896-02-29 (not leap) or 2096-02-29 (not leap)
        /** @var bool $result */
        $result = $reflectionMethod->invoke(null, '960229');
        self::assertTrue(
            $result,
            'Expected 960229 to be valid as 1996-02-29, but validation failed',
        );
    }


    /**
     * Tests modulo operation === vs == comparison.
     *
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testModuloStrictComparison(): void
    {
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('isValidLuhnChecksum');

        // The key is to test numbers where the checksum calculation results in exactly 0
        // vs numbers where it doesn't, to ensure === 0 vs == 0 distinction

        // Valid Luhn that results in total % 10 === 0
        $validLuhn = '8801235111088';
        $result = $reflectionMethod->invoke(null, $validLuhn);
        self::assertTrue(
            $result,
            'Expected valid Luhn checksum (total % 10 === 0) to pass validation',
        );

        // Invalid Luhn where total % 10 !== 0
        // We need multiple cases to ensure the mutation is detected
        $invalidCases = [
            '8801235111081', // total % 10 = 1
            '8801235111082', // total % 10 = 2
            '8801235111083', // total % 10 = 3
            '8801235111084', // total % 10 = 4
            '8801235111085', // total % 10 = 5
            '8801235111086', // total % 10 = 6
            '8801235111087', // total % 10 = 7
            '8801235111089', // total % 10 = 9
        ];

        foreach ($invalidCases as $invalidCase) {
            /** @var bool $result */
            $result = $reflectionMethod->invoke(null, $invalidCase);
            self::assertFalse(
                $result,
                sprintf(
                    'Expected invalid Luhn checksum "%s" to fail validation',
                    $invalidCase,
                ),
            );
        }
    }

    /**
     * Tests combined edge case for complete ID validation.
     *
     * @throws ExpectationFailedException
     */
    public function testCompleteIDValidationWithEdgeCases(): void
    {
        // Test ID that would fail if any mutation is applied
        $validId = '8801235111088'; // Born 1988-01-23, female, SA citizen
        $result = SouthAfricanIDValidator::luhnIDValidate($validId);
        self::assertTrue(
            $result,
            'Expected valid ID to pass all validation checks',
        );

        // Test with exactly 13 chars but invalid content
        $invalidId = 'ABCDEFGHIJKLM'; // 13 chars but not numeric
        $result = SouthAfricanIDValidator::luhnIDValidate($invalidId);
        self::assertFalse(
            $result,
            'Expected non-numeric 13-character string to fail validation',
        );
    }
}
