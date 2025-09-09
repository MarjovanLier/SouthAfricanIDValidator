<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the extractGender method in SouthAfricanIDValidator.
 */
final class ExtractGenderTest extends TestCase
{
    /**
     * Tests extractGender with various female sequence numbers.
     *
     * @param string $sequenceNumber The 4-digit sequence number.
     */
    #[DataProvider('femaleSequenceProvider')]
    public function testExtractGenderWithFemaleSequences(string $sequenceNumber): void
    {
        $baseId = '800101' . $sequenceNumber . '08';
        $sum = 0;
        $double = true;
        for ($i = 11; $i >= 0; --$i) {
            $digit = (int) $baseId[$i];
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double;
        }

        $checksum = (10 - ($sum % 10)) % 10;
        $idNumber = $baseId . $checksum;

        $result = SouthAfricanIDValidator::extractGender($idNumber);

        $this->assertSame('female', $result, sprintf('Sequence %s should indicate female', $sequenceNumber));
    }

    /**
     * Tests extractGender with various male sequence numbers.
     *
     * @param string $sequenceNumber The 4-digit sequence number.
     */
    #[DataProvider('maleSequenceProvider')]
    public function testExtractGenderWithMaleSequences(string $sequenceNumber): void
    {
        $baseId = '800101' . $sequenceNumber . '08';
        $sum = 0;
        $double = true;
        for ($i = 11; $i >= 0; --$i) {
            $digit = (int) $baseId[$i];
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double;
        }

        $checksum = (10 - ($sum % 10)) % 10;
        $idNumber = $baseId . $checksum;

        $result = SouthAfricanIDValidator::extractGender($idNumber);

        $this->assertSame('male', $result, sprintf('Sequence %s should indicate male', $sequenceNumber));
    }

    /**
     * Provides female sequence numbers for testing.
     *
     * @return string[][]
     *
     * @psalm-return list{list{'0000'}, list{'0001'}, list{'1234'}, list{'2500'}, list{'4998'}, list{'4999'}}
     */
    public static function femaleSequenceProvider(): array
    {
        return [
            ['0000'], // Minimum female
            ['0001'],
            ['1234'],
            ['2500'], // Middle
            ['4998'],
            ['4999'], // Maximum female
        ];
    }

    /**
     * Provides male sequence numbers for testing.
     *
     * @return string[][]
     *
     * @psalm-return list{list{'5000'}, list{'5001'}, list{'6789'}, list{'7500'}, list{'9998'}, list{'9999'}}
     */
    public static function maleSequenceProvider(): array
    {
        return [
            ['5000'], // Minimum male
            ['5001'],
            ['6789'],
            ['7500'], // Middle
            ['9998'],
            ['9999'], // Maximum male
        ];
    }

    /**
     * Tests extractGender with invalid ID length.
     */
    public function testExtractGenderWithInvalidLength(): void
    {
        $idNumber = '123456789'; // Too short

        $result = SouthAfricanIDValidator::extractGender($idNumber);

        $this->assertNull($result, 'Should return null for invalid length');
    }

    /**
     * Tests extractGender with non-numeric characters.
     */
    public function testExtractGenderWithNonNumericCharacters(): void
    {
        $idNumber = '80-01-01 5009-087'; // Valid male ID with formatting

        $result = SouthAfricanIDValidator::extractGender($idNumber);

        $this->assertSame('male', $result, 'Should extract gender after sanitisation');
    }

    /**
     * Tests extractGender at the boundary (4999/5000).
     */
    public function testExtractGenderAtBoundary(): void
    {
        // Test 4999 (female)
        $femaleId = '8001014999087';
        $result = SouthAfricanIDValidator::extractGender($femaleId);
        $this->assertSame('female', $result, '4999 should be female');

        // Test 5000 (male)
        $maleId = '8001015000087';
        $result = SouthAfricanIDValidator::extractGender($maleId);
        $this->assertSame('male', $result, '5000 should be male');
    }
}
