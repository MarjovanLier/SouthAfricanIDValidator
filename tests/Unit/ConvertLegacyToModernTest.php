<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the convertLegacyToModern method in SouthAfricanIDValidator.
 *
 * Tests the conversion of legacy South African ID numbers (with race indicators 0-7)
 * to modern format (with race indicator 8) including checksum recalculation.
 */
final class ConvertLegacyToModernTest extends TestCase
{
    /**
     * Tests conversion of a legacy ID with race indicator 0 (White).
     */
    public function testConvertLegacyWhiteIndicator(): void
    {
        $legacyId = '8001015009004'; // Valid ID with race indicator 0
        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        $this->assertIsString($result, 'convertLegacyToModern should return a string for valid legacy ID with race indicator 0');
        $this->assertStringStartsWith('80010150090', $result, 'The first 11 digits should remain unchanged');
        $this->assertSame('8', $result[11], 'Race indicator should be changed to 8 by default');
        $this->assertSame(13, \strlen($result), 'Result should be exactly 13 digits');
        $this->assertSame('8001015009087', $result, 'The complete converted ID should match expected');
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            'The converted ID should pass Luhn validation',
        );
    }

    /**
     * Tests conversion of a legacy ID with race indicator 1 (Cape Coloured).
     */
    public function testConvertLegacyCapeColouredIndicator(): void
    {
        $legacyId = '8001015009012'; // Valid ID with race indicator 1
        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        $this->assertIsString($result, 'convertLegacyToModern should return a string for valid legacy ID with race indicator 1');
        $this->assertStringStartsWith('80010150090', $result, 'The first 11 digits should remain unchanged');
        $this->assertSame('8', $result[11], 'Race indicator should be changed to 8 by default');
        $this->assertSame('8001015009087', $result, 'The complete converted ID should match expected');
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            'The converted ID should pass Luhn validation',
        );
    }

    /**
     * Tests conversion with explicit modern indicator 9.
     */
    public function testConvertLegacyWithIndicator9(): void
    {
        $legacyId = '8001015009004'; // Valid ID with race indicator 0
        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId, 9);

        $this->assertIsString($result, 'convertLegacyToModern should return a string when using indicator 9');
        $this->assertStringStartsWith('80010150090', $result, 'The first 11 digits should remain unchanged');
        $this->assertSame('9', $result[11], 'Race indicator should be changed to 9 when specified');
        $this->assertSame('8001015009095', $result, 'The complete converted ID with indicator 9 should match expected');
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            'The converted ID with indicator 9 should pass Luhn validation',
        );
    }

    /**
     * Tests that invalid modern indicator returns null.
     */
    public function testInvalidModernIndicatorReturnsNull(): void
    {
        $legacyId = '8001015009004'; // Valid ID with race indicator 0

        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId, 7);
        $this->assertNull($result, 'Invalid modern indicator 7 should return null');

        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId, 10);
        $this->assertNull($result, 'Invalid modern indicator 10 should return null');
    }

    /**
     * Tests conversion of legacy IDs with all race indicators 0-7.
     *
     * @param string $baseId        The first 11 digits of the ID.
     * @param string $raceIndicator The race indicator digit.
     */
    #[DataProvider('legacyRaceIndicatorProvider')]
    public function testConvertAllLegacyRaceIndicators(string $baseId, string $raceIndicator): void
    {
        // Create a valid legacy ID with the given race indicator
        $idWithoutChecksum = $baseId . $raceIndicator;
        $checksum = $this->calculateLuhnChecksum($idWithoutChecksum);
        $legacyId = $idWithoutChecksum . $checksum;

        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        $this->assertIsString(
            $result,
            "convertLegacyToModern should return a string for valid legacy ID with race indicator {$raceIndicator}",
        );
        /** @var string $result */
        /** @var non-empty-string $baseId */
        $this->assertStringStartsWith($baseId, $result, 'The first 11 digits should remain unchanged');
        $this->assertSame('8', $result[11], "Race indicator {$raceIndicator} should be changed to 8");
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            "The converted ID with original race indicator {$raceIndicator} should pass Luhn validation",
        );
    }

    /**
     * Provides test data for all legacy race indicators (0-7).
     *
     * @return string[][]
     *
     * @psalm-return list{list{'80010150090', '0'}, list{'80010150090', '1'}, list{'80010150090', '2'}, list{'80010150090', '3'}, list{'80010150090', '4'}, list{'80010150090', '5'}, list{'80010150090', '6'}, list{'80010150090', '7'}}
     */
    public static function legacyRaceIndicatorProvider(): array
    {
        return [
            ['80010150090', '0'], // White
            ['80010150090', '1'], // Cape Coloured
            ['80010150090', '2'], // Malay
            ['80010150090', '3'], // Griqua
            ['80010150090', '4'], // Chinese
            ['80010150090', '5'], // Indian
            ['80010150090', '6'], // Other Asian
            ['80010150090', '7'], // Other Coloured
        ];
    }

    /**
     * Tests that modern IDs with race indicator 8 are returned unchanged.
     */
    public function testModernIdWithIndicator8RemainsUnchanged(): void
    {
        $modernId = '8001015009087'; // Valid ID already has race indicator 8
        $result = SouthAfricanIDValidator::convertLegacyToModern($modernId);

        $this->assertSame($modernId, $result, 'Modern ID with race indicator 8 should remain unchanged');
    }

    /**
     * Tests that modern IDs with race indicator 9 are returned unchanged.
     */
    public function testModernIdWithIndicator9RemainsUnchanged(): void
    {
        $modernId = '8001015009095'; // Valid ID has race indicator 9
        $result = SouthAfricanIDValidator::convertLegacyToModern($modernId);

        $this->assertSame($modernId, $result, 'Modern ID with race indicator 9 should remain unchanged');
    }

    /**
     * Tests that invalid IDs return null.
     */
    public function testInvalidIdReturnsNull(): void
    {
        $invalidId = '1234567890123'; // Invalid ID
        $result = SouthAfricanIDValidator::convertLegacyToModern($invalidId);

        $this->assertNull($result, 'Invalid ID should return null');
    }

    /**
     * Tests that IDs with invalid length return null.
     */
    public function testInvalidLengthReturnsNull(): void
    {
        $shortId = '800101500908'; // Too short
        $result = SouthAfricanIDValidator::convertLegacyToModern($shortId);

        $this->assertNull($result, 'ID with invalid length should return null');
    }

    /**
     * Tests that IDs with invalid citizenship digit return null.
     */
    public function testInvalidCitizenshipReturnsNull(): void
    {
        $invalidCitizenshipId = '8001015009307'; // Citizenship digit 3 is invalid
        $result = SouthAfricanIDValidator::convertLegacyToModern($invalidCitizenshipId);

        $this->assertNull($result, 'ID with invalid citizenship digit should return null');
    }

    /**
     * Tests conversion with female sequence numbers (0000-4999).
     */
    public function testConvertFemaleSequenceNumber(): void
    {
        // Generate a valid female ID with race indicator 1
        $baseId = '800101499901';
        $checksum = $this->calculateLuhnChecksum($baseId);
        $femaleId = $baseId . $checksum;
        $result = SouthAfricanIDValidator::convertLegacyToModern($femaleId);

        $this->assertIsString($result, 'convertLegacyToModern should return a string for valid female ID');
        $this->assertStringStartsWith('80010149990', $result, 'The first 11 digits should remain unchanged');
        $this->assertSame('8', $result[11], 'Race indicator should be changed to 8');
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            'The converted female ID should pass Luhn validation',
        );
    }

    /**
     * Tests conversion with male sequence numbers (5000-9999).
     */
    public function testConvertMaleSequenceNumber(): void
    {
        // Generate a valid male ID with race indicator 0
        $baseId = '800101500000';
        $checksum = $this->calculateLuhnChecksum($baseId);
        $maleId = $baseId . $checksum;
        $result = SouthAfricanIDValidator::convertLegacyToModern($maleId);

        $this->assertIsString($result, 'convertLegacyToModern should return a string for valid male ID');
        $this->assertStringStartsWith('80010150000', $result, 'The first 11 digits should remain unchanged');
        $this->assertSame('8', $result[11], 'Race indicator should be changed to 8');
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            'The converted male ID should pass Luhn validation',
        );
    }

    /**
     * Tests conversion with different citizenship statuses.
     *
     * @param string $citizenship The citizenship digit.
     */
    #[DataProvider('citizenshipProvider')]
    public function testConvertWithDifferentCitizenshipStatuses(string $citizenship): void
    {
        $baseId = '8001015009' . $citizenship . '0';
        $checksum = $this->calculateLuhnChecksum($baseId);
        $legacyId = $baseId . $checksum;

        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        $this->assertIsString(
            $result,
            "convertLegacyToModern should return a string for ID with citizenship {$citizenship}",
        );
        /** @phpstan-ignore-next-line */
        if (!is_string($result)) {
            $this->fail('Result should be a string');
        }
        $this->assertSame($citizenship, $result[10], 'Citizenship digit should remain unchanged');
        $this->assertSame('8', $result[11], 'Race indicator should be changed to 8');
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            "The converted ID with citizenship {$citizenship} should pass Luhn validation",
        );
    }

    /**
     * Provides test data for different citizenship statuses.
     *
     * @return string[][]
     *
     * @psalm-return list{list{'0'}, list{'1'}, list{'2'}}
     */
    public static function citizenshipProvider(): array
    {
        return [
            ['0'], // South African citizen
            ['1'], // Permanent resident
            ['2'], // Refugee
        ];
    }

    /**
     * Tests checksum recalculation accuracy.
     */
    public function testChecksumRecalculationAccuracy(): void
    {
        // Test with a known legacy ID and expected modern result
        $legacyId = '8001015009004'; // Valid legacy ID with race indicator 0
        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        $this->assertIsString($result, 'Result should be a string');

        // Manually verify the checksum calculation
        $expectedBase = '800101500908';
        $this->assertStringStartsWith($expectedBase, $result, 'Base ID should match expected');

        // The checksum should be recalculated correctly
        // Note: The recalculated checksum might differ from original due to different race indicator
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($result),
            'The recalculated ID should pass Luhn validation',
        );
        $this->assertSame('8001015009087', $result, 'The complete ID should match expected result');
    }

    /**
     * Helper method to calculate Luhn checksum for testing.
     *
     * @param string $number The 12-digit number to calculate checksum for.
     *
     * @return string The calculated checksum digit.
     */
    private function calculateLuhnChecksum(string $number): string
    {
        $sum = 0;
        $double = true; // Start with doubling for 12th digit from right

        for ($i = \strlen($number) - 1; $i >= 0; --$i) {
            $digit = (int) $number[$i];

            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double;
        }

        return (string) ((10 - ($sum % 10)) % 10);
    }
}
