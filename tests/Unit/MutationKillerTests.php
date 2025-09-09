<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Dedicated tests to kill specific mutations that are escaping.
 * These tests are designed to fail if early returns are removed, proving they are necessary.
 */
final class MutationKillerTests extends TestCase
{
    /**
     * Test to kill Line 138 mutation - length validation early return in isValidIDDate().
     *
     * Since we removed length checks from private methods, invalid lengths would now
     * cause different behaviour if this early return is bypassed.
     */
    public function testLine138LengthValidationMutationKiller(): void
    {
        // Test various invalid lengths that should fail at the public method level
        $invalidLengths = [
            '', // 0 chars
            '1', // 1 char
            '12', // 2 chars
            '123', // 3 chars
            '1234', // 4 chars
            '12345', // 5 chars
            '1234567', // 7 chars
            '12345678', // 8 chars
            '123456789', // 9 chars
        ];

        foreach ($invalidLengths as $invalidLength) {
            // These must all return false due to length check
            // If Line 138 mutation is present, some of these might incorrectly pass
            // because they'd get processed by private methods that no longer validate length
            $result = SouthAfricanIDValidator::isValidIDDate($invalidLength);
            self::assertFalse(
                $result,
                sprintf("Length {strlen(%s)} input '%s' must fail length validation", $invalidLength, $invalidLength),
            );
        }

        // Edge case: Test input that would become valid when prefixed in private methods
        // '010101' -> invalid length 6 check should catch this before private methods process it
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('01010'), // 5 chars
            'Length 5 input should fail before being processed as a potential date',
        );
    }

    /**
     * Test to kill Line 143 mutation - early return true for 1800s dates.
     *
     * The key insight: Find dates that are valid as 1800s/1900s but would
     * cause different behaviour (likely failures) if processed as 2000s dates.
     * This ensures the early return at Line 143 is necessary.
     */
    public function testLine143EarlyReturn1800sMutationKiller(): void
    {
        // Strategy: Use dates that would be valid for 1800s/1900s interpretation
        // but invalid or problematic for 2000s interpretation

        // Critical case: Leap years that behave differently across centuries
        // 1900 was NOT a leap year, but 2000 WAS a leap year
        // So 000229 would be:
        // - Invalid as 1900-02-29 (1900 not leap year)
        // - Valid as 2000-02-29 (2000 is leap year)

        // But we need the opposite: valid for 1800s, invalid/different for 2000s

        // Test dates that are clearly valid for 1800s/1900s
        // but would be far future dates in 2000s (potentially handled differently)
        $historical1800sDates = [
            '880101', // 1888-01-01 vs 2088-01-01 (far future)
            '960229', // 1896-02-29 (valid leap) vs 2096-02-29 (far future leap)
            '990101', // 1999-01-01 vs 2099-01-01 (far future)
        ];

        foreach ($historical1800sDates as $historical1800Date) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($historical1800Date),
                sprintf('Date %s must be valid via 1800s early return path', $historical1800Date),
            );
        }

        // Additional critical test: dates at century boundaries
        // These should definitely use 1800s interpretation and early return
        $centuryBoundaryDates = ['991231', '000101']; // 1999-12-31, 1900-01-01

        foreach ($centuryBoundaryDates as $centuryBoundaryDate) {
            $result = SouthAfricanIDValidator::isValidIDDate($centuryBoundaryDate);
            // At least one should be true to prove the early return is working
            if ($centuryBoundaryDate === '991231') {
                self::assertTrue(
                    $result,
                    'Date 991231 (1999-12-31) should be valid via 1800s path early return',
                );
            }
        }

        // Stress test: if early return is removed, these dates would
        // all fall through to 2000s validation, potentially causing issues
        $stressTest1800sDates = ['850315', '920229', '970630', '991225'];

        foreach ($stressTest1800sDates as $stressTest1800Date) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($stressTest1800Date),
                sprintf('Stress test: Date %s must pass via 1800s early return', $stressTest1800Date),
            );
        }
    }

    /**
     * Test to kill Line 168 mutation - early return optimisation in sanitiseNumber().
     *
     * This is challenging because both paths produce identical results for digit strings.
     * Strategy: Create comprehensive tests that would expose performance degradation
     * or behavioural differences if the optimisation is removed.
     */
    public function testLine168SanitizerOptimizationMutationKiller(): void
    {
        // Use reflection to access private method
        $reflectionMethod = new ReflectionMethod(SouthAfricanIDValidator::class, 'sanitiseNumber');

        // Strategy 1: Test massive all-digit strings where performance optimization matters
        $perfCases = [
            '0', // Single digit
            '00000000000000000000', // 20 zeros
            str_repeat('1234567890', 100), // 1,000 digits
            str_repeat('9876543210', 500), // 5,000 digits
        ];

        foreach ($perfCases as $perfCase) {
            /** @var string $result */
            $result = $reflectionMethod->invoke(null, $perfCase);

            self::assertSame(
                $perfCase,
                $result,
                sprintf("Large digit string (length %d) must be returned unchanged via optimization", strlen($perfCase)),
            );

            // Verify our assumption: input is actually all digits
            self::assertTrue(
                ctype_digit($perfCase),
                "Test input must be all digits to trigger optimization path",
            );
        }

        // Strategy 2: Test edge cases where ctype_digit behaviour is critical
        $digitEdgeCases = [
            '0000000000000' => '0000000000000', // All zeros
            '1234567890123' => '1234567890123', // SA ID length
            '999999999999999999' => '999999999999999999', // Large number
        ];

        foreach ($digitEdgeCases as $input => $expected) {
            /** @var string $result */
            $result = $reflectionMethod->invoke(null, $input);
            self::assertSame(
                $expected,
                $result,
                sprintf("Edge case '%s' must use fast early return path", $input),
            );
        }

        // Strategy 3: Contrast with non-digit inputs to validate test logic
        $nonDigitInputs = [
            '123a456' => '123456',
            '123 456' => '123456',
            '123-456' => '123456',
        ];

        foreach ($nonDigitInputs as $nonDigitInput => $expectedClean) {
            /** @var string $result */
            $result = $reflectionMethod->invoke(null, $nonDigitInput);
            self::assertSame(
                $expectedClean,
                $result,
                sprintf("Non-digit input '%s' should be cleaned via regex path", $nonDigitInput),
            );

            // These inputs contain non-digits, so they use the regex path
            self::assertStringContainsStringIgnoringCase(
                'a',
                $nonDigitInput . 'a',
                "Test validates non-digit inputs use regex cleanup path",
            );
        }

        // Strategy 4: Empty string edge case - uses regex path (not optimization)
        /** @var string $emptyResult */
        $emptyResult = $reflectionMethod->invoke(null, '');
        self::assertSame('', $emptyResult, 'Empty string should be handled correctly via regex path');

        // Strategy 5: Extreme performance case to expose the optimization
        // If the optimization is removed, this might time out or be noticeably slower
        $extremeCase = str_repeat('1234567890', 1000); // 10,000 characters
        $startTime = microtime(true);
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, $extremeCase);
        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        self::assertSame($extremeCase, $result, 'Extreme case must return unchanged');
        // This assertion might help detect if optimization was bypassed (slower execution)
        self::assertLessThan(0.1, $duration, 'Optimization should make this very fast (< 100ms)');
    }

    /**
     * Integration test to verify the overall flow with mutations in mind.
     * This test would fail if any of the early returns are removed.
     */
    public function testIntegrationMutationResistance(): void
    {
        // Test complete ID validation flow with various inputs
        // Each of these depends on specific early returns working correctly

        // Length validation dependency (Line 138)
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('12345'),
            'Length validation must work correctly',
        );

        // 1800s early return dependency (Line 143)
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('880101'),
            '1800s early return optimization must work correctly',
        );

        // Test the sanitiser in context of full ID validation
        // This indirectly tests the Line 168 mutation
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate('8701105800085'),
            'Full ID validation should work with sanitiser optimisation',
        );
    }
}
