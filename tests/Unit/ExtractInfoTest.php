<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the extractInfo method in SouthAfricanIDValidator.
 */
final class ExtractInfoTest extends TestCase
{
    /**
     * Tests extractInfo with a valid modern male South African citizen ID.
     */
    public function testExtractInfoWithValidModernMaleId(): void
    {
        $idNumber = '8001015009087'; // Valid ID: male, citizen, modern format

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertTrue($idValidationResult['valid'], 'ID should be valid');
        self::assertIsArray($idValidationResult['date_components'], 'Date components should be an array');
        self::assertSame('80', $idValidationResult['date_components']['year'], 'Year should be 80');
        self::assertSame('01', $idValidationResult['date_components']['month'], 'Month should be 01');
        self::assertSame('01', $idValidationResult['date_components']['day'], 'Day should be 01');
        self::assertSame('male', $idValidationResult['gender'], 'Gender should be male');
        self::assertSame('south_african_citizen', $idValidationResult['citizenship'], 'Should be SA citizen');
        self::assertFalse($idValidationResult['is_legacy'], 'Should not be legacy format');
        self::assertSame('unspecified', $idValidationResult['race_indicator'], 'Race indicator should be unspecified (modern format)');
    }

    /**
     * Tests extractInfo with a valid legacy female permanent resident ID.
     */
    public function testExtractInfoWithValidLegacyFemaleId(): void
    {
        // Generate a valid legacy ID with female sequence, permanent resident
        $baseId = '800101499910'; // Female (4999), permanent resident (1), legacy (0)
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

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertTrue($idValidationResult['valid'], 'ID should be valid');
        self::assertSame('female', $idValidationResult['gender'], 'Gender should be female');
        self::assertSame('permanent_resident', $idValidationResult['citizenship'], 'Should be permanent resident');
        self::assertTrue($idValidationResult['is_legacy'], 'Should be legacy format');
        self::assertSame('white', $idValidationResult['race_indicator'], 'Race indicator should be white (legacy apartheid classification)');
    }

    /**
     * Tests extractInfo with an invalid ID number.
     */
    public function testExtractInfoWithInvalidId(): void
    {
        $idNumber = '1234567890123'; // Invalid ID

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertFalse($idValidationResult['valid'], 'ID should be invalid');
        self::assertNull($idValidationResult['date_components'], 'Date components should be null');
        self::assertNull($idValidationResult['gender'], 'Gender should be null');
        self::assertNull($idValidationResult['citizenship'], 'Citizenship should be null');
        self::assertFalse($idValidationResult['is_legacy'], 'Should not be legacy format');
        self::assertNull($idValidationResult['race_indicator'], 'Race indicator should be null');
    }

    /**
     * Tests extractInfo with a refugee ID.
     */
    public function testExtractInfoWithRefugeeId(): void
    {
        $idNumber = '8001015009285'; // Valid 13-digit ID: Male (5009), refugee (2), modern (8)

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertTrue($idValidationResult['valid'], 'ID should be valid');
        self::assertSame('refugee', $idValidationResult['citizenship'], 'Should be refugee');
        self::assertSame('male', $idValidationResult['gender'], 'Gender should be male');
    }

    /**
     * Tests extractInfo with ID containing non-numeric characters.
     */
    public function testExtractInfoWithNonNumericCharacters(): void
    {
        $idNumber = '80-01-01 5009-087'; // Valid ID with formatting

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertTrue($idValidationResult['valid'], 'ID should be valid after sanitisation');
        self::assertIsArray($idValidationResult['date_components'], 'Date components should be an array');
        self::assertNotNull($idValidationResult['date_components'], 'Date components should not be null');

        $dateComponents = $idValidationResult['date_components'];
        self::assertArrayHasKey('year', $dateComponents, 'Date components should have year');
        self::assertSame('80', $dateComponents['year'], 'Year should be extracted correctly');

        self::assertSame('male', $idValidationResult['gender'], 'Gender should be extracted correctly');
    }

    /**
     * Tests extractInfo with too short ID.
     */
    public function testExtractInfoWithTooShortId(): void
    {
        $idNumber = '80010150090'; // Too short

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertFalse($idValidationResult['valid'], 'ID should be invalid');
        self::assertNull($idValidationResult['date_components'], 'Date components should be null');
        self::assertNull($idValidationResult['gender'], 'Gender should be null');
    }
}
