<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Tests designed to break equivalent mutations by finding edge cases where
 * the mutated code would actually behave differently from the original.
 *
 * These tests target the 3 specific escaped mutations that appear to be equivalent
 * but may have subtle behavioural differences under specific conditions.
 */
final class EquivalentMutationBreakerTest extends TestCase
{
    /**
     * CRITICAL: Test for Line 138 - The length check removal mutation.
     *
     * The key insight: If length validation is removed from isValidIDDate(),
     * invalid length strings would be passed to isValidDateFor1800sOr1900s()
     * and isValidDateFor2000s(). These methods would then pass malformed
     * strings to StringManipulation::isValidDate(), which might behave unexpectedly.
     */
    public function testLine138LengthValidationCriticalPath(): void
    {
        // The mutation removes: if (\strlen($date) !== 6) { return false; }
        // This would cause invalid length strings to reach private methods

        // Strategy: Test strings that would cause StringManipulation issues
        // if passed without proper length validation

        // Case 1: Empty string would become '18' or '20' in StringManipulation
        // StringManipulation::isValidDate('18', 'Ymd') -> false (format mismatch)
        // StringManipulation::isValidDate('20', 'Ymd') -> false (format mismatch)
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate(''),
            'Empty string MUST fail length check. If passes, Line 138 mutation escaped!',
        );

        // Case 2: Very specific length that could cause format confusion
        // '12345' (5 chars) would become:
        // - '1812345' in 1800s method -> DateTime::createFromFormat('Ymd', '1812345') -> might parse as 1812-34-5?
        // - '2012345' in 2000s method -> DateTime::createFromFormat('Ymd', '2012345') -> might parse as 2012-34-5?
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('12345'),
            'Five-char string MUST fail length check. If passes, Line 138 mutation escaped!',
        );

        // Case 3: Seven characters that might accidentally parse
        // '1234567' would become '181234567' or '201234567' -> potentially valid but wrong interpretation
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('1234567'),
            'Seven-char string MUST fail length check. If passes, Line 138 mutation escaped!',
        );

        // Case 4: The most dangerous case - 4 characters that could become valid
        // '0101' -> '180101' (1801-01-01) or '200101' (2001-01-01) both potentially valid dates
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('0101'),
            'CRITICAL: Four-char "0101" MUST fail length check. If passes as valid date, Line 138 mutation definitely escaped!',
        );

        // Case 5: Another dangerous 4-char case
        // '1225' -> '181225' (1812-25) invalid month or '201225' (2012-25) invalid month -> should fail
        // BUT: '1212' -> '181212' (1812-12) or '201212' (2012-12) -> valid dates!
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('1212'),
            'CRITICAL: Four-char "1212" MUST fail length check. If passes as valid date, Line 138 mutation definitely escaped!',
        );
    }

    /**
     * CRITICAL: Test for Line 143 - The 1800s early return removal mutation.
     *
     * The key insight: If the early return `return true;` is removed from the 1800s check,
     * dates that are valid for 1800s would fall through to 2000s validation.
     * We need dates that are valid for 1800s but invalid for 2000s.
     */
    public function testLine143EarlyReturnRemovalDetection(): void
    {
        // The mutation removes: if (self::isValidDateFor1800sOr1900s($date)) { return true; }
        // This would cause valid 1800s dates to also be checked against 2000s validation

        // Strategy: Find dates that are valid as 1800s/1900s but invalid as 2000s
        // This is complex because the methods only validate date format, not ranges

        // Key insight: Both validation methods use StringManipulation::isValidDate()
        // The difference is the century prefix: '18' vs '20'

        // Case 1: Test dates that would be valid for both centuries
        // These should pass with or without the mutation (not helpful for detection)

        // Case 2: Test dates that would be invalid for both centuries
        // These should fail with or without the mutation (not helpful for detection)

        // Case 3: The critical case - dates that might be valid for 1800s but not 2000s
        // Due to leap year differences or other calendar rules

        // Most years are the same, but there could be edge cases with leap years
        // 1900 was NOT a leap year (divisible by 100, not by 400)
        // 2000 WAS a leap year (divisible by 400)

        // So 000229 would be:
        // - Invalid as 1900-02-29 (1900 not a leap year)
        // - Valid as 2000-02-29 (2000 is a leap year)

        // But we need the opposite case. Let's try century year boundaries:

        // Test dates that should definitely be valid via 1800s path
        $validHistoricalDates = [
            '880101', // 1888-01-01 - clearly historical, valid
            '960229', // 1896-02-29 - leap year, valid
            '991231', // 1999-12-31 - valid
        ];

        foreach ($validHistoricalDates as $validHistoricalDate) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($validHistoricalDate),
                sprintf('Date %s MUST be valid via 1800s path. ', $validHistoricalDate) .
                "If fails, Line 143 early return may have been bypassed and date rejected by 2000s validation!",
            );
        }

        // The challenge: Both 18880101 and 20880101 are valid dates to DateTime
        // So this mutation might actually be equivalent in most cases

        // Try to find a date that would be valid when prefixed with '18' but invalid with '20'
        // This is very unlikely since both are just format validations

        // Alternative strategy: At least verify the expected valid dates work
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('880101'),
            'MUTATION INDICATOR: 880101 should be valid. Test ensures 1800s path is working.',
        );
    }

    /**
     * CRITICAL: Test for Line 168 - The sanitiseNumber optimisation removal.
     *
     * This is the most challenging mutation because both code paths produce
     * identical results for all inputs. However, we can try to detect performance
     * differences or create edge cases.
     */
    public function testLine168SanitizerOptimizationRemovalDetection(): void
    {
        // The mutation removes: if (\ctype_digit($number)) { return $number; }
        // This would force all inputs through the preg_replace path

        // Strategy 1: Performance detection (may be unreliable in unit tests)
        $reflectionMethod = new ReflectionMethod(SouthAfricanIDValidator::class, 'sanitiseNumber');

        // Test with a very large all-digit string
        $largeDigitString = str_repeat('1234567890', 10000); // 100,000 characters

        $startTime = microtime(true);
        $result = $reflectionMethod->invoke(null, $largeDigitString);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        self::assertSame($largeDigitString, $result, 'Large string should be unchanged');

        // If the optimization is bypassed, regex processing might be noticeably slower
        // This is heuristic and may not be reliable across different systems
        self::assertLessThan(
            1.0, // 1 second threshold
            $executionTime,
            sprintf('PERFORMANCE INDICATOR: Execution took %ss. ', $executionTime)
            . "If significantly slow, Line 168 optimization may have been bypassed. "
            . "Note: This may vary by system performance.",
        );

        // Strategy 2: Edge case testing
        // Test boundary conditions where ctype_digit might behave differently than regex
        $edgeCases = [
            '', // Empty string: ctype_digit('') = false, preg_replace returns ''
            '0', // Single zero
            '00000000000000000000000000000', // Many zeros
        ];

        foreach ($edgeCases as $edgeCase) {
            /** @var string $result */
            $result = $reflectionMethod->invoke(null, $edgeCase);
            self::assertSame(
                $edgeCase,
                $result,
                sprintf("Edge case '%s' should be handled identically by both paths", $edgeCase),
            );
        }

        // Strategy 3: Consistency verification
        // Ensure that for all-digit inputs, the result is always identical to input
        $allDigitInputs = ['123', '456789', '0000', '1234567890123'];

        foreach ($allDigitInputs as $allDigitInput) {
            /** @var string $result */
            $result = $reflectionMethod->invoke(null, $allDigitInput);
            self::assertSame(
                $allDigitInput,
                $result,
                sprintf("All-digit input '%s' MUST be returned unchanged. ", $allDigitInput) .
                "Any modification suggests unexpected behaviour from Line 168 mutation.",
            );
        }
    }

    /**
     * Meta-test: Verify our understanding of the mutations by testing known behaviours.
     */
    public function testMutationBehaviourVerification(): void
    {
        // Verify that valid 6-character dates work (baseline test)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('880101'),
            'Baseline: Valid 6-char date should work',
        );

        // Verify that invalid length dates fail (this should kill Line 138 mutation)
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('12345'),
            'Baseline: Invalid length should fail',
        );

        // Verify sanitiser works for clean input (baseline for Line 168)
        $reflectionMethod = new ReflectionMethod(SouthAfricanIDValidator::class, 'sanitiseNumber');
        $result = $reflectionMethod->invoke(null, '123456');
        self::assertSame('123456', $result, 'Baseline: Clean digits should remain unchanged');

        // If these baseline tests pass but mutations still escape,
        // the mutations might be truly equivalent (no behavioural difference)
    }
}
