<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Tests crafted to kill specific mutations in the Luhn checksum implementation.
 *
 * Targets:
 * 1) $digit = (int) $number[$i];  →  $digit = $number[$i];
 *    - Ensures digits are used as integers in arithmetic operations.
 * 2) $total += $digit;  →  $total -= $digit;
 *    - Ensures the checksum uses addition and not subtraction.
 *
 * Uses real South African ID numbers where arithmetic differences are observable.
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator
 */
final class KillMutationsTest extends TestCase
{
    /**
     * Ensures the Luhn checksum uses addition and not subtraction (+= vs -=).
     *
     * If the mutation changes += to -=, these valid IDs will fail the checksum
     * and this test will fail.
     *
     * @throws ExpectationFailedException
     */
    public function testLuhnUsesAdditionNotSubtractionOnValidSouthAfricanIds(): void
    {
        $validIds = [
            // Well-formed, known-valid SA IDs used throughout the suite
            '8001015009087',
            '8801235111088',
            '9912310001083',
            // Many zeros to exercise doubling and subtraction-of-9 logic
            '0001010000089',
        ];

        foreach ($validIds as $validId) {
            self::assertTrue(
                SouthAfricanIDValidator::luhnIDValidate($validId),
                sprintf(
                    'Valid SA ID %s should pass Luhn with addition; subtraction mutation would break it.',
                    $validId,
                ),
            );
        }

        // Also assert a minimal Luhn-positive case directly via the private method
        // to make the arithmetic direction unmistakable for the mutator.
        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('isValidLuhnChecksum');
        self::assertTrue(
            $reflectionMethod->invoke(null, '26'),
            "Minimal Luhn '26' must be valid using addition, not subtraction.",
        );
    }

    /**
     * Ensures per-digit processing treats characters as integers during checksum calculations.
     *
     * convertLegacyToModern recalculates the checksum using bit shifts; without casting
     * the per-character digit to int, PHP would not perform the arithmetic correctly.
     * This kills the mutation removing the explicit cast on the per-digit extraction.
     *
     * @throws ExpectationFailedException
     */
    public function testChecksumDigitProcessingRequiresIntCastDuringConversion(): void
    {
        // Valid legacy ID (race indicator 0) with known modern conversion outcome
        $legacyId = '8001015009004';
        $converted = SouthAfricanIDValidator::convertLegacyToModern($legacyId, 8);

        self::assertSame(
            '8001015009087',
            $converted,
            'Conversion must compute checksum arithmetically using integer digits.',
        );
    }

    /**
     * Reinforces that the Luhn checksum treats string digits as integers using a real SA ID.
     *
     * This directly calls the private checksum method via reflection to focus on the
     * digit-casting path in the core loop.
     *
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testIsValidLuhnChecksumTreatsDigitsAsIntegersWithRealId(): void
    {
        $idNumber = '8001015009087';

        $reflectionClass = new ReflectionClass(SouthAfricanIDValidator::class);
        $reflectionMethod = $reflectionClass->getMethod('isValidLuhnChecksum');

        self::assertTrue(
            $reflectionMethod->invoke(null, $idNumber),
            'Digits must be treated as integers in checksum calculation for real IDs.',
        );
    }
}
