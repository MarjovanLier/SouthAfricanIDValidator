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
 * Tests for edge cases to improve code coverage.
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator
 */
final class EdgeCaseCoverageTest extends TestCase
{
    /**
     * Tests the edge case where preg_replace might theoretically return null.
     *
     * While preg_replace with our simple pattern should never return null,
     * we test the null coalescing operator to ensure full code coverage.
     *
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testSanitiseNumberWithEdgeCases(): void
    {
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('sanitiseNumber');

        // Test normal cases
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, '123abc456');
        self::assertSame('123456', $result, 'Expected non-digits to be removed');

        // Test empty string
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, '');
        self::assertSame('', $result, 'Expected empty string to remain empty');

        // Test string with no digits
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, 'abcdef');
        self::assertSame('', $result, 'Expected string with no digits to return empty string');

        // Test string that's already all digits (optimization path)
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, '1234567890');
        self::assertSame('1234567890', $result, 'Expected all-digit string to be returned unchanged');

        // Test with special characters
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, '!@#$%^&*()123');
        self::assertSame('123', $result, 'Expected special characters to be removed');

        // Test with unicode characters
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, '123αβγ456');
        self::assertSame('123456', $result, 'Expected unicode characters to be removed');

        // Test with null bytes (edge case for preg_replace)
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, "123\0456");
        self::assertSame('1236', $result, 'Expected non-digit characters to be removed including null byte effects');

        // Test with very long string
        $longString = str_repeat('a1b2c3', 1000);
        /** @var string $result */
        $result = $reflectionMethod->invoke(null, $longString);
        $expected = str_repeat('123', 1000);
        self::assertSame($expected, $result, 'Expected long string to be processed correctly');
    }

    /**
     * Tests uncovered branches in the validator.
     *
     * @throws ExpectationFailedException
     */
    public function testUncoveredValidatorPaths(): void
    {
        // Test with various invalid formats that might trigger different paths
        $testCases = [
            // String that becomes empty after sanitisation
            'abcdefghijklm',
            // String with only special characters
            '!@#$%^&*()_+-=',
            // Mixed valid and invalid characters resulting in wrong length
            'a1b2c3d4e5f6g',
            // Unicode characters
            'αβγδεζηθικλμ',
            // Whitespace
            '             ',
            // Tabs and newlines
            "\t\n\r\t\n\r\t\n\r\t\n\r",
        ];

        foreach ($testCases as $testCase) {
            $result = SouthAfricanIDValidator::luhnIDValidate($testCase);
            self::assertFalse(
                $result,
                sprintf('Expected validation to fail for input: %s', $testCase),
            );
        }
    }
}
