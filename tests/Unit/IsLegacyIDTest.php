<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the isLegacyID method in SouthAfricanIDValidator.
 */
final class IsLegacyIDTest extends TestCase
{
    /**
     * Tests isLegacyID with legacy race indicators (0-7).
     *
     * @param string $raceIndicator The race indicator digit.
     */
    #[DataProvider('legacyIndicatorProvider')]
    public function testIsLegacyIdWithLegacyIndicators(string $raceIndicator): void
    {
        $baseId = '80010150090' . $raceIndicator;
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

        $result = SouthAfricanIDValidator::isLegacyID($idNumber);

        $this->assertTrue($result, sprintf('Race indicator %s should indicate legacy ID', $raceIndicator));
    }

    /**
     * Tests isLegacyID with modern race indicators (8-9).
     *
     * @param string $raceIndicator The race indicator digit.
     */
    #[DataProvider('modernIndicatorProvider')]
    public function testIsLegacyIdWithModernIndicators(string $raceIndicator): void
    {
        $baseId = '80010150090' . $raceIndicator;
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

        $result = SouthAfricanIDValidator::isLegacyID($idNumber);

        $this->assertFalse($result, sprintf('Race indicator %s should indicate modern ID', $raceIndicator));
    }

    /**
     * Provides legacy race indicator values (0-7).
     *
     * @return string[][]
     *
     * @psalm-return list{list{'0'}, list{'1'}, list{'2'}, list{'3'}, list{'4'}, list{'5'}, list{'6'}, list{'7'}}
     */
    public static function legacyIndicatorProvider(): array
    {
        return [
            ['0'], // White
            ['1'], // Cape Coloured
            ['2'], // Malay
            ['3'], // Griqua
            ['4'], // Chinese
            ['5'], // Indian
            ['6'], // Other Asian
            ['7'], // Other Coloured
        ];
    }

    /**
     * Provides modern race indicator values (8-9).
     *
     * @return string[][]
     *
     * @psalm-return list{list{'8'}, list{'9'}}
     */
    public static function modernIndicatorProvider(): array
    {
        return [
            ['8'], // Standard modern format
            ['9'], // Alternative modern format
        ];
    }

    /**
     * Tests isLegacyID with invalid ID length.
     */
    public function testIsLegacyIdWithInvalidLength(): void
    {
        $idNumber = '123456789'; // Too short

        $result = SouthAfricanIDValidator::isLegacyID($idNumber);

        $this->assertFalse($result, 'Should return false for invalid length');
    }

    /**
     * Tests isLegacyID with non-numeric characters in legacy ID.
     */
    public function testIsLegacyIdWithNonNumericCharactersLegacy(): void
    {
        $idNumber = '80-01-01 5009-004'; // Valid legacy ID with formatting (race indicator 0)

        $result = SouthAfricanIDValidator::isLegacyID($idNumber);

        $this->assertTrue($result, 'Should identify legacy ID after sanitisation');
    }

    /**
     * Tests isLegacyID with non-numeric characters in modern ID.
     */
    public function testIsLegacyIdWithNonNumericCharactersModern(): void
    {
        $idNumber = '80-01-01 5009-087'; // Valid modern ID with formatting (race indicator 8)

        $result = SouthAfricanIDValidator::isLegacyID($idNumber);

        $this->assertFalse($result, 'Should identify modern ID after sanitisation');
    }

    /**
     * Tests isLegacyID with edge case empty string.
     */
    public function testIsLegacyIdWithEmptyString(): void
    {
        $result = SouthAfricanIDValidator::isLegacyID('');

        $this->assertFalse($result, 'Should return false for empty string');
    }
}
