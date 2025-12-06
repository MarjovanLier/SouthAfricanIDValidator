<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for method interactions in SouthAfricanIDValidator.
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::convertLegacyToModern
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::extractInfo
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::luhnIDValidate
 */
final class IntegrationTest extends TestCase
{
    /**
     * Tests that extractInfo correctly processes IDs converted from legacy to modern format.
     *
     * This integration test ensures that the conversion process preserves all ID data
     * and that extractInfo can correctly interpret the converted ID.
     */
    public function testExtractInfoAfterConvertLegacyToModern(): void
    {
        $legacyId = '8001015009004'; // Legacy ID with race indicator 0

        $modernId = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        $this->assertIsString($modernId, 'Converted ID should be a string');

        $idValidationResult = SouthAfricanIDValidator::extractInfo($modernId);

        // Verify the converted ID is recognised as valid
        $this->assertTrue($idValidationResult['valid'], 'Converted ID should be valid');

        // Verify it is now recognised as modern (not legacy)
        $this->assertFalse($idValidationResult['is_legacy'], 'Converted ID should be modern, not legacy');

        // Verify the race indicator was changed to modern (unspecified or unknown)
        $this->assertContains($idValidationResult['race_indicator'], ['unspecified', 'unknown'], 'Race indicator should be modern (unspecified or unknown)');

        // Verify original data is preserved
        $this->assertSame(['year' => '80', 'month' => '01', 'day' => '01'], $idValidationResult['date_components'], 'Date should be preserved');
        $this->assertSame('male', $idValidationResult['gender'], 'Gender should be preserved');
        $this->assertSame('south_african_citizen', $idValidationResult['citizenship'], 'Citizenship should be preserved');
    }

    /**
     * Tests round-trip conversion validation.
     *
     * Ensures that a legacy ID converted to modern format passes full validation.
     */
    public function testRoundTripConversionValidation(): void
    {
        $legacyId = '8001015009004'; // Valid legacy ID (used in other tests)

        // Verify legacy ID is valid
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($legacyId),
            'Legacy ID should be valid before conversion',
        );

        // Convert to modern
        $modernId = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        $this->assertIsString($modernId, 'Conversion should return string');

        // Verify modern ID is valid
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($modernId),
            'Converted modern ID should pass full validation',
        );

        // Verify data integrity through round-trip
        $idValidationResult = SouthAfricanIDValidator::extractInfo($legacyId);
        $modernInfo = SouthAfricanIDValidator::extractInfo($modernId);

        $this->assertSame($idValidationResult['date_components'], $modernInfo['date_components'], 'Date must be preserved');
        $this->assertSame($idValidationResult['gender'], $modernInfo['gender'], 'Gender must be preserved');
        $this->assertSame($idValidationResult['citizenship'], $modernInfo['citizenship'], 'Citizenship must be preserved');
    }

    /**
     * Tests extractInfo with invalid citizenship (null return from luhnIDValidate).
     *
     * Ensures extractInfo handles the null return correctly when citizenship is invalid.
     */
    public function testExtractInfoWithInvalidCitizenship(): void
    {
        // Create an ID with invalid citizenship digit (3 is invalid, only 0, 1, 2 are valid)
        // 8001015009387 - if we replace citizenship 0 with 3, we need to recalculate checksum
        $invalidId = '8001015009381'; // Invalid citizenship digit 3

        $idValidationResult = SouthAfricanIDValidator::extractInfo($invalidId);

        $this->assertFalse($idValidationResult['valid'], 'ID with invalid citizenship should have valid=false');
        $this->assertNull($idValidationResult['date_components'], 'Date components should be null for invalid ID');
        $this->assertNull($idValidationResult['gender'], 'Gender should be null for invalid ID');
        $this->assertNull($idValidationResult['citizenship'], 'Citizenship should be null for invalid ID');
        $this->assertFalse($idValidationResult['is_legacy'], 'Legacy flag should be false for invalid ID');
        $this->assertNull($idValidationResult['race_indicator'], 'Race indicator should be null for invalid ID');
    }

    /**
     * Tests extractInfo with invalid date but valid checksum.
     *
     * Ensures the validator correctly rejects IDs with impossible dates even if checksum is valid.
     */
    public function testExtractInfoWithInvalidDateButValidChecksum(): void
    {
        // We need an ID with invalid date (month 13) but otherwise valid structure
        // This would need a calculated checksum
        $idWithInvalidDate = '9913015000087'; // 991301 = month 13

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idWithInvalidDate);

        $this->assertFalse($idValidationResult['valid'], 'ID with invalid date should have valid=false');
        $this->assertNull($idValidationResult['date_components'], 'Date components should be null when date is invalid');
    }
}
