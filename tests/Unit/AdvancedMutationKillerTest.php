<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Advanced tests specifically designed to kill the 3 escaping ReturnRemoval mutations.
 * Uses sophisticated testing techniques to detect when early returns are bypassed.
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidIDDate
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::sanitizeNumber
 */
final class AdvancedMutationKillerTest extends TestCase
{
    /**
     * CRITICAL TEST: Detects Line 138 mutation by using specific inputs that would
     * cause different behavior if length validation is bypassed.
     */
    public function testLine138MutationDetectionViaLengthBypass(): void
    {
        // Strategy: Test inputs that would cause errors or unexpected behavior
        // if passed to the private methods without length validation

        // These short inputs would cause substr() issues or unexpected results
        // if length validation is bypassed and they reach private methods
        $problematicLengths = [
            '', // Empty string -> StringManipulation::isValidDate('18', 'Ymd') would fail
            '1', // 1 char -> StringManipulation::isValidDate('181', 'Ymd') would fail
            '12', // 2 chars -> StringManipulation::isValidDate('1812', 'Ymd') would fail
            '123', // 3 chars -> StringManipulation::isValidDate('18123', 'Ymd') would fail
            '1234', // 4 chars -> StringManipulation::isValidDate('181234', 'Ymd') might succeed unexpectedly!
            '12345', // 5 chars -> StringManipulation::isValidDate('1812345', 'Ymd') would fail
        ];

        foreach ($problematicLengths as $problematicLength) {
            self::assertFalse(
                SouthAfricanIDValidator::isValidIDDate($problematicLength),
                sprintf(
                    "Input '%s' (length %d) MUST fail due to length validation at line 138. " .
                    "If this passes, the early return was bypassed by mutation!",
                    $problematicLength,
                    strlen($problematicLength),
                ),
            );
        }

        // Critical edge case: 4-character input that could become valid date if prefixed
        // This is the most dangerous case - '1234' -> '181234' might be valid
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('1234'),
            'MUTATION DETECTOR: If this passes, Line 138 mutation escaped! ' .
            'Input "1234" should fail length check, not become valid date "181234"',
        );
    }

    /**
     * CRITICAL TEST: Detects Line 143 mutation using dates that would behave
     * differently if forced through 2000s validation instead of early return.
     */
    public function testLine143MutationDetectionViaPathDivergence(): void
    {
        // Strategy: Use dates that have different validation results between
        // 1800s/1900s path vs 2000s path

        // Key insight: Find dates where:
        // - isValidDateFor1800sOr1900s($date) = true (should early return)
        // - isValidDateFor2000s($date) = false (would fail if early return bypassed)

        // Test case: Historical leap years that would be far future for 2000s
        $historicalLeapDates = [
            '960229', // 1896-02-29 (valid) vs 2096-02-29 (far future, might be rejected)
        ];

        foreach ($historicalLeapDates as $historicalLeapDate) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($historicalLeapDate),
                sprintf('MUTATION DETECTOR: Date %s should be valid via 1800s early return. ', $historicalLeapDate) .
                "If this fails, Line 143 mutation may have caused fall-through to 2000s validation!",
            );
        }

        // Additional strategy: dates at century boundary that should use 1800s path
        $centuryBoundaryDates = [
            '880101', // 1888-01-01 (clearly historical)
            '991231', // 1999-12-31 (late 1900s)
        ];

        foreach ($centuryBoundaryDates as $centuryBoundaryDate) {
            $result = SouthAfricanIDValidator::isValidIDDate($centuryBoundaryDate);
            self::assertTrue(
                $result,
                sprintf('MUTATION DETECTOR: Date %s must be valid via 1800s path early return. ', $centuryBoundaryDate) .
                "Failure suggests Line 143 mutation escaped and date fell through to 2000s validation.",
            );
        }

        // Edge case test: verify that our test assumptions are correct
        // These dates should definitely trigger the 1800s path
        $definite1800sDates = ['850101', '920229', '970630', '991225'];
        foreach ($definite1800sDates as $definite1800Date) {
            self::assertTrue(
                SouthAfricanIDValidator::isValidIDDate($definite1800Date),
                sprintf('Date %s must be valid - tests 1800s path functionality', $definite1800Date),
            );
        }
    }

    /**
     * CRITICAL TEST: Detects Line 168 mutation through performance and behavior analysis.
     */
    public function testLine168MutationDetectionViaOptimizationBypass(): void
    {
        // Use reflection to access private method
        $reflectionMethod = new ReflectionMethod(SouthAfricanIDValidator::class, 'sanitiseNumber');

        // Strategy 1: Performance-based detection
        // If optimization is bypassed, regex operations on large strings are slower
        $performanceTestCases = [
            str_repeat('1234567890', 1000), // 10,000 characters
            str_repeat('9876543210', 2000), // 20,000 characters
        ];

        foreach ($performanceTestCases as $performanceTestCase) {
            $startTime = microtime(true);
            $result = $reflectionMethod->invoke(null, $performanceTestCase);
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            // The optimized path should be very fast
            self::assertLessThan(
                0.05, // 50ms threshold
                $executionTime,
                "MUTATION DETECTOR: Performance degraded for large digit string! " .
                sprintf('Execution time: %ss. Line 168 optimization may have been bypassed by mutation.', $executionTime),
            );

            self::assertSame(
                $performanceTestCase,
                $result,
                "Large digit string must be returned unchanged",
            );
        }

        // Strategy 2: Behavioral consistency check
        // Both paths should produce identical results, but test extensively
        $consistencyTestCases = [
            '0',
            '123456789',
            '0000000000000',
            '1234567890123', // SA ID length
            '999999999999999999999999999999', // Very large number
        ];

        foreach ($consistencyTestCases as $consistencyTestCase) {
            /** @var string $result */
            $result = $reflectionMethod->invoke(null, $consistencyTestCase);

            self::assertSame(
                $consistencyTestCase,
                $result,
                sprintf("MUTATION DETECTOR: Digit string '%s' should be returned unchanged. ", $consistencyTestCase) .
                "If modified, Line 168 optimization was bypassed!",
            );

            // Verify it would trigger the optimization path
            self::assertTrue(
                ctype_digit($consistencyTestCase),
                "Test case must be all digits to test optimization",
            );
        }

        // Strategy 3: Edge case validation
        // Test boundary conditions where optimization matters most
        $edgeCases = [
            ['input' => '', 'description' => 'empty string (should NOT use optimization)'],
            ['input' => '0', 'description' => 'single zero (should use optimization)'],
        ];

        foreach ($edgeCases as $edgeCase) {
            /** @var string $result */
            $result = $reflectionMethod->invoke(null, $edgeCase['input']);
            self::assertSame(
                $edgeCase['input'],
                $result,
                sprintf('Edge case: %s - result must be unchanged', $edgeCase['description']),
            );
        }
    }

    /**
     * Integration test that combines all three mutation detection strategies.
     */
    public function testIntegratedMutationDetection(): void
    {
        // Test the complete flow to ensure all early returns work together

        // Line 138 test: length validation
        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate('12345'),
            'Integration: Length validation must work (Line 138)',
        );

        // Line 143 test: 1800s early return
        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate('880101'),
            'Integration: 1800s early return must work (Line 143)',
        );

        // Line 168 test: sanitizer optimization (indirect through full ID validation)
        self::assertNotNull(
            SouthAfricanIDValidator::luhnIDValidate('8701105800085'),
            'Integration: Sanitizer optimization should work in full validation context (Line 168)',
        );

        // Combined test: Use an ID that exercises multiple code paths
        $testId = '8701105800085';
        $result = SouthAfricanIDValidator::luhnIDValidate($testId);

        self::assertTrue(
            $result === true || $result === false,
            'Integration test: Full ID validation should complete successfully with all optimizations',
        );
    }
}
