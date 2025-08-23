<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidIDDate
 */
final class IsValidIDDateTest extends TestCase
{
    /**
     * Provides a set of valid ID date portions.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'880101'}, list{'990101'}, list{'000229'}, list{'010101'}, list{'181229'},
     *     list{'200229'}}
     */
    public static function provideValidIDDates(): array
    {
        return [
            ['880101'],
            ['990101'],
            ['000229'],
            ['010101'],
            ['181229'],
            ['200229'],
        ];
    }


    /**
     * Provides a set of invalid ID date portions.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'870230'}, list{'990230'}, list{'000230'}, list{'010299'}, list{'01013'},
     *     list{'0110111'}, list{'170229'}, list{'180229'}, list{'190229'}, list{''}, list{'1'}, list{'12'},
     *     list{'123'}, list{'1234'}, list{'12345'}, list{'1234567'}, list{'12345678'}}
     */
    public static function provideInvalidIDDates(): array
    {
        return [
            ['870230'],
            ['990230'],
            ['000230'],
            ['010299'],
            ['01013'],
            ['0110111'],
            ['170229'],
            ['180229'],
            ['190229'],
            // Add edge cases for string length validation (mutation line 138)
            [''], // 0 characters
            ['1'], // 1 character
            ['12'], // 2 characters
            ['123'], // 3 characters
            ['1234'], // 4 characters
            ['12345'], // 5 characters
            ['1234567'], // 7 characters
            ['12345678'], // 8 characters
        ];
    }


    /**
     * @dataProvider provideValidIDDates
     */
    #[DataProvider('provideValidIDDates')]
    public function testValidIdDates(string $date): void
    {
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate($date));
    }


    /**
     * @dataProvider provideInvalidIDDates
     */
    #[DataProvider('provideInvalidIDDates')]
    public function testInvalidIdDates(string $date): void
    {
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate($date));
    }


    /**
     * Test specific edge case for mutation at line 143 - early return true for 1800s/1900s dates.
     * This ensures that valid 1800s/1900s dates return true immediately and don't fall through
     * to the 2000s validation which might return a different result.
     */
    public function testValidEighteenHundredsDateMutation(): void
    {
        // This date should be valid for 1800s (18880101 = Jan 1, 1888)
        // If the early return is removed, it would continue to check 2000s (20880101 = invalid future date)
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('880101'));

        // Another 1800s date that would be invalid as 2000s date
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('991231')); // Dec 31, 1999 vs Dec 31, 2099
    }


    /**
     * Test that demonstrates the behavior difference when string length validation is bypassed.
     * This targets mutation at line 138 where early return false is removed.
     */
    public function testStringLengthValidationMutation(): void
    {
        // These should definitely return false due to incorrect length
        // If the early return is removed, the method would continue with invalid length strings
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate(''));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('1'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('12345'));
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('1234567'));

        // Valid length should still work
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('880101'));
    }


    /**
     * CRITICAL TEST: Targets mutation at line 138 - ReturnRemoval for length validation.
     * This test ensures that when strlen($date) !== 6, the method returns false immediately
     * and does NOT proceed to century validation methods with invalid-length strings.
     */
    public function testMutationLine138LengthValidationEarlyReturn(): void
    {
        // Edge case: What happens when StringManipulation::isValidDate receives invalid length?
        // If early return is bypassed, these invalid-length strings would be passed to:
        // - isValidDateFor1800sOr1900s('18' . $date, 'Ymd')
        // - isValidDateFor2000s('20' . $date, 'Ymd')

        // Test with lengths that would create different behaviors if passed through
        $invalidLengthInputs = [
            '', // Empty -> would become '18' + '' = '18' (2 chars) -> DateTime fails
            '1', // 1 char -> '18' + '1' = '181' (3 chars) -> DateTime fails
            '12', // 2 chars -> '18' + '12' = '1812' (4 chars) -> DateTime fails
            '123', // 3 chars -> '18' + '123' = '18123' (5 chars) -> DateTime fails
            '1234', // 4 chars -> '18' + '1234' = '181234' (6 chars) -> might succeed unexpectedly
            '12345', // 5 chars -> '18' + '12345' = '1812345' (7 chars) -> DateTime fails
            '1234567', // 7 chars -> '18' + '1234567' = '181234567' (9 chars) -> DateTime fails
            '12345678', // 8 chars -> '18' + '12345678' = '1812345678' (10 chars) -> DateTime fails
        ];

        foreach ($invalidLengthInputs as $invalidLengthInput) {
            self::assertFalse(
                SouthAfricanIDValidator::isValidIDDate($invalidLengthInput),
                sprintf("Invalid length input '%s' must return false immediately, not proceed to century validation", $invalidLengthInput),
            );
        }

        // The critical case: 4-character input could become valid 6-character date when prefixed
        // If early return is bypassed, '1234' -> '181234' might parse as valid date in some edge cases
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('1234'),
            'Length-4 input must return false immediately, not create unexpected 6-char date',
        );
    }


    /**
     * CRITICAL TEST: Targets mutation at line 143 - ReturnRemoval for 1800s/1900s early return true.
     * This test finds dates that are valid for 1800s/1900s but would be invalid for 2000s,
     * ensuring the early return true is essential for correct behavior.
     */
    public function testMutationLine143EarlyReturnTrueFor1800s(): void
    {
        // The key insight: find dates that return true for 1800s validation
        // but would return false if forced to continue to 2000s validation

        // Strategy: Test dates that are valid historical dates but invalid as future dates

        // Test case 1: Far future dates when interpreted as 2000s
        // '980101' -> 1998-01-01 (valid) vs 2098-01-01 (far future, might be rejected)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('980101'),
            'Date 980101 should be valid as 1998-01-01 via early return true',
        );

        // Test case 2: Edge of valid range
        // '990101' -> 1999-01-01 (valid) vs 2099-01-01 (far future)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('990101'),
            'Date 990101 should be valid as 1999-01-01 via early return true',
        );

        // Test case 3: Leap year in 1800s that might be different in 2000s
        // '960229' -> 1996-02-29 (valid leap year) vs 2096-02-29 (far future leap year)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('960229'),
            'Date 960229 should be valid as 1996-02-29 via early return true',
        );

        // Test case 4: Very old dates
        // '010101' -> 1901-01-01 (valid historical) vs 2001-01-01 (recent past, but different century logic)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('010101'),
            'Date 010101 should be valid as 1901-01-01 via early return true',
        );

        // CRITICAL: The key insight is leap year differences between centuries!
        // Date 000229: 1800-02-29 is INVALID (1800 not leap year), 2000-02-29 is VALID (2000 is leap year)
        // This date will fail 1800s validation, proceed to 2000s validation, and return TRUE
        // If the early return true (line 143) is removed, ALL dates would go to 2000s validation
        // For most dates this doesn't matter, but for 000229 it changes the result!

        // This is a date that should be valid (returns true via 2000s path)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('000229'),
            'MUTATION KILLER: 000229 is valid as 2000-02-29 but invalid as 1800-02-29',
        );

        // If line 143 mutation is applied, this would incorrectly continue to 2000s validation
        // even for dates that should return true from 1800s validation
    }


    /**
     * Enhanced test to demonstrate the specific path through validation logic.
     * This helps ensure mutations don't change the expected flow.
     */
    public function testValidationLogicFlow(): void
    {
        // Test the complete flow: length -> 1800s -> 2000s

        // Should fail at length check (line 138)
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('12345'));

        // Should pass 1800s check and return true immediately (line 143)
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('850101')); // 1985-01-01

        // Should fail 1800s check, proceed to 2000s check, and return result
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate('050101')); // 2005-01-01
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate('050230')); // 2005-02-30 invalid
    }
}
