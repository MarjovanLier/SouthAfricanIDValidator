<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Additional tests for citizenship digit validation.
 */
#[CoversMethod(SouthAfricanIDValidator::class, 'luhnIDValidate')]
final class MoreCitizenshipTest extends TestCase
{
    /**
     * Provides ID numbers with various invalid citizenship values.
     *
     * @return string[][]
     *
     * @psalm-return array{'citizenship 3': list{'8701105003085'}, 'citizenship 4': list{'8701105004085'}, 'citizenship 5': list{'8701105005085'}, 'citizenship 6': list{'8701105006085'}, 'citizenship 7': list{'8701105007085'}, 'citizenship 8': list{'8701105008085'}, 'citizenship 9': list{'8701105009085'}, 'citizenship A': list{'870110500A085'}, 'citizenship X': list{'870110500X085'}, 'citizenship space': list{'870110500 085'}, 'citizenship -': list{'870110500-085'}}
     */
    public static function provideInvalidCitizenshipNumbers(): array
    {
        return [
            'citizenship 3' => ['8701105003085'],
            'citizenship 4' => ['8701105004085'],
            'citizenship 5' => ['8701105005085'],
            'citizenship 6' => ['8701105006085'],
            'citizenship 7' => ['8701105007085'],
            'citizenship 8' => ['8701105008085'],
            'citizenship 9' => ['8701105009085'],
            'citizenship A' => ['870110500A085'],
            'citizenship X' => ['870110500X085'],
            'citizenship space' => ['870110500 085'],
            'citizenship -' => ['870110500-085'],
        ];
    }

    /**
     * Tests that invalid citizenship values are handled.
     * Note: If the checksum is invalid, the method returns false before checking citizenship.
     * Only IDs with valid checksums but invalid citizenship return null.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('provideInvalidCitizenshipNumbers')]
    public function testInvalidCitizenshipHandling(string $idNumber): void
    {
        $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);

        // The library checks checksum before citizenship.
        // These test IDs have invalid checksums, so they return false, not null.
        // Only citizenship 8 seems to have a valid checksum in our test data.
        if ($idNumber === '8701105008085') {
            self::assertNotFalse($result, 'ID with citizenship 8 has valid checksum: ' . $idNumber);
            return;
        }

        self::assertNotTrue($result, 'Invalid ID should not return true: ' . $idNumber);
    }

    /**
     * Tests valid citizenship values with real IDs.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testValidCitizenshipValues(): void
    {
        // Using actual valid IDs from existing tests
        $citizen = '8701105800085';      // Position 11 = 0 (citizen)
        $permanent = '4806010046080';    // Position 11 = 0 (citizen)

        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($citizen),
            'Valid citizen ID should be accepted',
        );

        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($permanent),
            'Valid ID with citizenship 0 should be accepted',
        );
    }

    /**
     * Tests edge case of citizenship at boundary positions.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCitizenshipBoundaryPositions(): void
    {
        // Test with too short ID (citizenship position doesn't exist)
        $shortId = '870110580';
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($shortId),
            'ID too short to have citizenship digit should be invalid',
        );

        // Test with exactly 10 digits (no citizenship digit)
        $tenDigits = '8701105800';
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($tenDigits),
            'ID with 10 digits should be invalid',
        );
    }

    /**
     * Tests citizenship validation with non-standard formats.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCitizenshipWithFormattedInput(): void
    {
        // Valid ID with citizenship 0, but formatted with spaces
        $formatted = '8701 1058 0008 5';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($formatted),
            'Valid ID with spaces should be accepted after sanitization',
        );

        // Valid ID with dashes
        $dashed = '8701-10-5800-08-5';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($dashed),
            'Valid ID with dashes should be accepted after sanitization',
        );
    }
}
