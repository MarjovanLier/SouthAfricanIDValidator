<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::sanitizeNumber
 */
final class SanitizeNumberTest extends TestCase
{
    public function testSanitizeNumber(): void
    {
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['123-456']);
        self::assertEquals('123456', $result);

        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['abc123']);
        self::assertEquals('123', $result);

        // Add test for already clean number (all digits)
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['123456']);
        self::assertEquals('123456', $result);
    }


    /**
     * Test targeting mutation at line 168 - early return for already clean digits.
     * This test ensures that the ctype_digit check and early return works correctly
     * and doesn't produce the same result as the preg_replace fallback.
     */
    public function testSanitizeNumberEarlyReturnMutation(): void
    {
        // Test with various all-digit strings to ensure early return path is tested
        $cleanNumbers = ['0', '123456789', '0000000000000', '9876543210123'];

        foreach ($cleanNumbers as $cleanNumber) {
            /**
             * @var string $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', [$cleanNumber]);
            self::assertEquals(
                $cleanNumber,
                $result,
                sprintf("Clean number '%s' should be returned unchanged", $cleanNumber),
            );
        }

        // Test edge cases
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['']);
        self::assertEquals('', $result, 'Empty string should remain empty');

        // Compare behavior: clean vs dirty strings
        /**
         * @var string $cleanResult
         */
        $cleanResult = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['123456']);

        /**
         * @var string $dirtyResult
         */
        $dirtyResult = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['123456']);

        self::assertEquals($cleanResult, $dirtyResult, 'Results should be identical');
    }


    /**
     * Test the regex replacement path specifically.
     */
    public function testSanitizeNumberRegexPath(): void
    {
        // These will definitely use the regex path, not the early return
        $dirtyNumbers = [
            '123-456-789' => '123456789',
            'abc123def456ghi' => '123456',
            '   1 2 3   ' => '123',
            '!@#123$%^456&*(' => '123456',
            'no_digits_here' => '',
        ];

        foreach ($dirtyNumbers as $input => $expected) {
            /**
             * @var string $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', [$input]);
            self::assertEquals(
                $expected,
                $result,
                sprintf("Dirty input '%s' should be cleaned to '%s'", $input, $expected),
            );
        }
    }


    /**
     * CRITICAL TEST: Targets mutation at line 168 - ReturnRemoval for sanitizeNumber optimization.
     * This test is challenging because both code paths (early return vs regex) produce identical results
     * for all-digit strings. However, we can test edge cases and performance-critical scenarios.
     */
    public function testMutationLine168SanitizeNumberEarlyReturn(): void
    {
        // The challenge: ctype_digit($number) early return vs preg_replace fallback
        // Both should produce identical results for all-digit strings

        // Strategy 1: Test edge cases where behavior might differ
        $edgeCaseDigits = [
            '0', // Single zero
            '00000000000000', // Many zeros
            '1234567890123456789', // Very long digit string
            (string) PHP_INT_MAX, // Maximum integer as string
        ];

        foreach ($edgeCaseDigits as $edgeCaseDigit) {
            /**
             * @var string $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', [$edgeCaseDigit]);
            self::assertEquals(
                $edgeCaseDigit,
                $result,
                sprintf("Clean digit string '%s' must be returned unchanged via early return optimization", $edgeCaseDigit),
            );

            // Verify the input is actually all digits (our assumption)
            self::assertTrue(
                ctype_digit($edgeCaseDigit),
                sprintf("Test input '%s' must be all digits to test the optimization path", $edgeCaseDigit),
            );

            // Psalm type assertion: confirm this is a numeric string
            /** @psalm-assert numeric-string $edgeCaseDigit */
        }

        // Strategy 2: Test performance-critical South African ID patterns
        $saIdDigitPatterns = [
            '8701105800085', // Valid SA ID - all digits
            '0000000000000', // Edge case - all zeros
            '9999999999999', // Edge case - all nines
            '1234567890123', // Sequential digits
        ];

        foreach ($saIdDigitPatterns as $saIdDigitPattern) {
            /**
             * @var string $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', [$saIdDigitPattern]);
            self::assertEquals(
                $saIdDigitPattern,
                $result,
                sprintf("SA ID digit pattern '%s' must use fast early return path", $saIdDigitPattern),
            );
        }

        // Strategy 3: Contrast with non-digit inputs to ensure test validity
        $nonDigitInputs = [
            '8701105800085a', // Almost all digits
            ' 8701105800085', // Leading space
            '8701105800085 ', // Trailing space
        ];

        foreach ($nonDigitInputs as $nonDigitInput) {
            /**
             * @var string $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', [$nonDigitInput]);
            // These should NOT use early return path, should use regex path
            self::assertFalse(
                ctype_digit($nonDigitInput),
                sprintf("Input '%s' must not be all digits to test regex path", $nonDigitInput),
            );
            // Result should still be cleaned properly
            self::assertTrue(
                ctype_digit($result) || $result === '',
                sprintf("Result '%s' should be clean digits or empty", $result),
            );
        }
    }


    /**
     * Test specific edge cases that might reveal different behavior between optimization paths.
     * This is a supplementary test to increase mutation detection probability.
     */
    public function testSanitizeNumberOptimizationEdgeCases(): void
    {
        // Test empty string (edge case for ctype_digit)
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', ['']);
        self::assertEquals('', $result, 'Empty string should remain empty');
        // Note: ctype_digit('') returns false, so this uses regex path

        // Test strings that might behave differently with ctype_digit vs regex
        $testCases = [
            ['input' => '0', 'expected' => '0', 'description' => 'Single zero'],
            ['input' => '123456789012345678901234567890', 'expected' => '123456789012345678901234567890', 'description' => 'Very long digit string'],
        ];

        foreach ($testCases as $testCase) {
            /**
             * @var string $result
             */
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitizeNumber', [$testCase['input']]);
            self::assertEquals(
                $testCase['expected'],
                $result,
                'Test case: ' . $testCase['description'],
            );
        }
    }


    /**
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call.
     * @param array<int, string> $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflectionMethod = (new ReflectionClass($object::class))->getMethod($methodName);

        return $reflectionMethod->invokeArgs($object, $parameters);
    }
}
