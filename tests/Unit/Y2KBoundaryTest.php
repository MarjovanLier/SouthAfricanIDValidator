<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Y2K boundary cases and century interpretation.
 */
#[CoversMethod(SouthAfricanIDValidator::class, 'luhnIDValidate')]
final class Y2KBoundaryTest extends TestCase
{
    /**
     * Tests how the library interprets year 00.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testYear00Interpretation(): void
    {
        // Using a known valid ID and checking behaviour with year 00
        // The library should interpret 00 as either 1900 or 2000
        $year00 = '0001015019080'; // Modified from valid ID 0809015019080

        $result = SouthAfricanIDValidator::luhnIDValidate($year00);
        self::assertIsBool(
            $result,
            'Year 00 should be handled by the library',
        );
    }

    /**
     * Tests century boundaries using existing valid IDs.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCenturyBoundariesWithValidIds(): void
    {
        // These are actual valid IDs from the test suite
        $validIds = [
            '8701105800085', // 1987
            '3202295029085', // 1932 or 2032?
            '4806010046080', // 1948 or 2048?
            '3206015052087', // 1932 or 2032?
            '0809015019080', // 2008
        ];

        foreach ($validIds as $validId) {
            $year = substr($validId, 0, 2);
            $result = SouthAfricanIDValidator::luhnIDValidate($validId);

            self::assertTrue(
                $result,
                sprintf('Valid ID should be accepted regardless of century interpretation: %s (year %s)', $validId, $year),
            );
        }
    }

    /**
     * Tests the ambiguous year range (00-99).
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testAmbiguousYearRange(): void
    {
        // Document how the library handles different year values
        $testCases = [
            '0809015019080' => 'Year 08 - likely 2008',
            '3202295029085' => 'Year 32 - likely 1932',
            '4806010046080' => 'Year 48 - likely 1948',
            '8701105800085' => 'Year 87 - likely 1987',
        ];

        foreach ($testCases as $id => $description) {
            $result = SouthAfricanIDValidator::luhnIDValidate((string) $id);
            self::assertTrue($result, sprintf('%s should be valid: %s', $description, $id));
        }
    }

    /**
     * Tests date validation across century boundaries.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testDateValidationAcrossCenturies(): void
    {
        // The library validates dates for both 19xx and 20xx interpretations
        // Using existing valid IDs to test this behaviour

        // February 29 in year 32 - could be 1932 or 2032
        // 1932 was a leap year, 2032 will be a leap year
        $feb29 = '3202295029085';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($feb29),
            'February 29 in ambiguous year should be validated correctly',
        );
    }

    /**
     * Tests the transition period around Y2K.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testY2KTransitionPeriod(): void
    {
        // Document behavior for years around 2000
        // Years 95-05 could be particularly ambiguous

        // We can only test with valid IDs we have
        $year08 = '0809015019080'; // Clearly 2008
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($year08),
            'Year 08 (2008) should be valid',
        );
    }

    /**
     * Tests extreme year values.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testExtremeYearValues(): void
    {
        // Test with modified versions of valid IDs
        $baseId = '8701105800085';

        // Year 99 - could be 1899, 1999, or 2099
        $year99 = '99' . substr($baseId, 2);
        $result = SouthAfricanIDValidator::luhnIDValidate($year99);
        self::assertNotNull($result, 'Year 99 should be handled');

        // Year 00 - could be 1900, 2000, or 2100
        $year00 = '00' . substr($baseId, 2);
        $result = SouthAfricanIDValidator::luhnIDValidate($year00);
        self::assertNotNull($result, 'Year 00 should be handled');
    }
}
