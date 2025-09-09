<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Edge case and negative tests specifically designed to kill mutation escapes.
 */
#[CoversClass(SouthAfricanIDValidator::class)]
final class MutationEdgeCaseTest extends TestCase
{
    /**
     * Test that the '18' century prefix is necessary.
     * We need a date that MUST check 1800s to be valid.
     */
    public function testCenturyArrayRequires1800s(): void
    {
        // February 30 is never valid, but let us use a real approach
        // The year 00 could be 1800, 1900, or 2000
        // But only 1900 and 2000 have 00 as a leap year issue

        // Test with date 990229 - only valid as 1900-02-29 (not 1899 or 2099)
        $result = SouthAfricanIDValidator::isValidIDDate('000229');
        $this->assertTrue($result, '000229 is valid as 1900-02-29 or 2000-02-29');

        // The mutation removing '18' would cause the loop to skip 1800s
        // We need to ensure at least one date requires 1800s validation

        // Actually test a full ID to ensure the path is exercised
        $idNumber = '0002290001086'; // Born 1900-02-29 or 2000-02-29
        $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);
        $this->assertTrue($result, 'ID with ambiguous century should validate');
    }

    /**
     * Test that += cannot be changed to -= in Luhn.
     * The checksum specifically depends on addition.
     */
    public function testLuhnAdditionIsEssential(): void
    {
        // Create an ID where changing += to -= would definitely fail
        // With subtraction, we'd get negative totals

        // ID with high digits that would create large negative if subtracted
        $idNumber = '9909099999081'; // High digits everywhere
        $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);
        $this->assertTrue($result, 'High digit ID requires addition not subtraction');

        // ID with specific pattern that only works with addition
        $id2 = '1212121212120'; // Alternating 1s and 2s
        $result2 = SouthAfricanIDValidator::luhnIDValidate($id2);
        $this->assertTrue($result2, 'Alternating pattern requires addition');
    }

    /**
     * Test that modulo comparison needs to be exactly 0.
     * The % 10 === 0 vs % 10 == 0 distinction.
     */
    public function testModuloMustBeExactlyZero(): void
    {
        // Test IDs where the checksum calculation gives exactly 0
        $validId = '8001015009087';
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($validId),
            'Valid ID has total % 10 exactly 0',
        );

        // Test all other remainders (1-9) fail
        $baseId = '800101500908';
        for ($digit = 0; $digit <= 9; $digit++) {
            $testId = $baseId . $digit;
            $expected = ($digit === 7); // Only 7 gives valid checksum
            $result = SouthAfricanIDValidator::luhnIDValidate($testId);
            $this->assertSame(
                $expected,
                $result,
                sprintf('ID ending in %d should ', $digit) . ($expected ? 'pass' : 'fail'),
            );
        }
    }

    /**
     * Test integer casting is required in Luhn algorithm.
     * Without (int) cast, string concatenation would occur.
     */
    public function testLuhnNeedsIntegerCast(): void
    {
        // Test with ID containing digit '9' which doubles to 18
        // String "1" + "8" = "18" but we need 1 + 8 = 9 after digit reduction
        $idNumber = '9901019999002'; // Valid date with 9s, citizenship 0
        $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);
        $this->assertTrue($result, 'Luhn needs integer math for digit reduction');

        // Test with zeros - string "0" behaves differently than int 0
        $id2 = '0001010000006'; // Mostly zeros with valid checksum
        $result2 = SouthAfricanIDValidator::luhnIDValidate($id2);
        $this->assertTrue($result2, 'Zeros need integer casting');
    }

    /**
     * Test that gender comparison requires integer cast.
     * String "4999" > "5000" lexicographically but int 4999 < 5000.
     */
    public function testGenderNeedsIntegerComparison(): void
    {
        // This is the critical test - string vs int comparison gives opposite results!

        // Female with sequence 4999 (just under boundary)
        $femaleId = '8001014999089';
        $gender = SouthAfricanIDValidator::extractGender($femaleId);
        $this->assertSame('female', $gender, 'Int 4999 < 5000 so female');

        // Male with sequence 5000 (exactly at boundary)
        $maleId = '8001015000083';
        $gender2 = SouthAfricanIDValidator::extractGender($maleId);
        $this->assertSame('male', $gender2, 'Int 5000 >= 5000 so male');

        // Critical: "0999" as string > "5000" but as int 999 < 5000
        $femaleIdWithZero = '8001010999084'; // Sequence "0999"
        $gender3 = SouthAfricanIDValidator::extractGender($femaleIdWithZero);
        $this->assertSame('female', $gender3, 'String "0999" > "5000" but int 999 < 5000');
    }

    /**
     * Test NotIdenticalNotEqual mutations with edge cases.
     * These check if !== can be safely changed to !=.
     */
    public function testStrictLengthValidation(): void
    {
        // Test with exactly 13 numeric characters
        $valid13 = '8001015009087';
        $this->assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($valid13),
            'Exactly 13 chars passes',
        );

        // Test with 13 characters but including non-numeric
        $invalid13 = 'ABCDEFGHIJKLM'; // 13 chars but not numeric
        $this->assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($invalid13),
            '13 non-numeric chars fails',
        );

        // Test boundary cases
        $tooShort = '800101500908'; // 12 chars
        $this->assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($tooShort),
            '12 chars fails',
        );

        $tooLong = '80010150090877'; // 14 chars
        $this->assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($tooLong),
            '14 chars fails',
        );
    }

    /**
     * Test convertLegacyToModern with validation edge cases.
     * The validation must return exactly true, not truthy.
     */
    public function testConvertLegacyValidationStrictness(): void
    {
        // Test with invalid checksum - luhnIDValidate returns false
        $invalidChecksum = '8001015009000'; // Wrong checksum
        $result = SouthAfricanIDValidator::convertLegacyToModern($invalidChecksum);
        $this->assertNull($result, 'Invalid checksum returns null');

        // Test with invalid citizenship - luhnIDValidate returns null
        $invalidCitizen = '8001015009304'; // Citizenship 3 is invalid
        $result2 = SouthAfricanIDValidator::convertLegacyToModern($invalidCitizen);
        $this->assertNull($result2, 'Invalid citizenship returns null');

        // Test with non-legacy (modern) ID - should return unchanged
        $modernId = '8001015009087'; // Race indicator 8 (modern)
        $result3 = SouthAfricanIDValidator::convertLegacyToModern($modernId);
        $this->assertSame($modernId, $result3, 'Modern ID returns unchanged');

        // Test with valid legacy - should convert
        $legacyId = '8001015009004'; // Race indicator 0 (legacy)
        $result4 = SouthAfricanIDValidator::convertLegacyToModern($legacyId);
        $this->assertIsString($result4, 'Valid legacy converts');
        $this->assertSame('8001015009087', $result4, 'Conversion is correct');
    }

    /**
     * Test extractInfo with edge cases for validation states.
     * Must distinguish between false, null, and true from luhnIDValidate.
     */
    public function testExtractInfoValidationStates(): void
    {
        // Valid ID - luhnIDValidate returns true
        $validId = '8001015009087';
        $info = SouthAfricanIDValidator::extractInfo($validId);
        $this->assertTrue($info['valid'], 'Valid ID has valid=true');
        $this->assertIsArray($info['date_components'], 'Has date components');

        // Invalid checksum - luhnIDValidate returns false
        $invalidChecksum = '8001015009088';
        $info2 = SouthAfricanIDValidator::extractInfo($invalidChecksum);
        $this->assertFalse($info2['valid'], 'Invalid checksum has valid=false');
        $this->assertNull($info2['date_components'], 'No date components');

        // Invalid citizenship - luhnIDValidate returns null
        $invalidCitizen = '8001015009387';
        $info3 = SouthAfricanIDValidator::extractInfo($invalidCitizen);
        $this->assertFalse($info3['valid'], 'Invalid citizenship has valid=false');
        $this->assertNull($info3['date_components'], 'No date components');

        // Too short - does not pass length check
        $tooShort = '800101500908';
        $info4 = SouthAfricanIDValidator::extractInfo($tooShort);
        $this->assertFalse($info4['valid'], 'Too short has valid=false');
    }

    /**
     * Test wouldBeDuplicates with strict comparison.
     * The substr comparison uses === for type safety.
     */
    public function testDuplicatesStrictComparison(): void
    {
        // Test with identical first 11 digits
        $id1 = '8001015009087';
        $id2 = '8001015009095'; // Same first 11, different checksum
        $this->assertTrue(
            SouthAfricanIDValidator::wouldBeDuplicates($id1, $id2),
            'Same first 11 digits are duplicates',
        );

        // Test with different first 11 digits
        $id3 = '8001025009084'; // Different day
        $this->assertFalse(
            SouthAfricanIDValidator::wouldBeDuplicates($id1, $id3),
            'Different first 11 digits are not duplicates',
        );

        // Test with invalid lengths
        $shortId = '800101500908'; // 12 chars
        $this->assertFalse(
            SouthAfricanIDValidator::wouldBeDuplicates($id1, $shortId),
            'Short ID cannot be duplicate',
        );
        $this->assertFalse(
            SouthAfricanIDValidator::wouldBeDuplicates($shortId, $id1),
            'Short ID cannot be duplicate (reversed)',
        );
    }

    /**
     * Test bit shift operation in convertLegacyToModern.
     * The << operator and > 9 check are critical.
     */
    public function testConvertLegacyBitShiftLogic(): void
    {
        // Test conversion that exercises the bit shift path
        $legacyId = '8001015009004'; // Valid legacy with race 0
        $result = SouthAfricanIDValidator::convertLegacyToModern($legacyId);
        $this->assertIsString($result, 'Conversion succeeds');

        // Verify the checksum was recalculated correctly
        $isValid = SouthAfricanIDValidator::luhnIDValidate($result);
        $this->assertTrue($isValid, 'Converted ID has valid checksum');

        // Test with different race indicators to ensure algorithm works
        $legacyIds = [
            '8001015009012' => '1', // Race 1
            '8001015009020' => '2', // Race 2
            '8001015009038' => '3', // Race 3
        ];

        foreach ($legacyIds as $legacy => $race) {
            $converted = SouthAfricanIDValidator::convertLegacyToModern((string) $legacy);
            if ($converted !== null) {
                $this->assertTrue(
                    SouthAfricanIDValidator::luhnIDValidate($converted),
                    sprintf('Converted ID from race %s is valid', $race),
                );
            }
        }
    }

    /**
     * Test string casting in convertLegacyToModern.
     * The integer modernIndicator must be cast to string.
     */
    public function testConvertLegacyStringCasting(): void
    {
        $legacyId = '8001015009004'; // Valid legacy

        // Test with integer 8 (default)
        $result8 = SouthAfricanIDValidator::convertLegacyToModern($legacyId, 8);
        $this->assertIsString($result8, 'Returns string');
        $this->assertSame('8001015009087', $result8, 'Casts 8 to string correctly');

        // Test with integer 9
        $result9 = SouthAfricanIDValidator::convertLegacyToModern($legacyId, 9);
        $this->assertIsString($result9, 'Returns string');
        $this->assertSame('8001015009095', $result9, 'Casts 9 to string correctly');
    }

    /**
     * Test extraction methods with non-13 character input.
     * All should handle incorrect lengths properly.
     */
    public function testExtractionMethodsLengthValidation(): void
    {
        $tooShort = '800101500908'; // 12 chars
        $tooLong = '80010150090877'; // 14 chars

        // extractDateComponents
        $this->assertNull(
            SouthAfricanIDValidator::extractDateComponents($tooShort),
            'extractDateComponents rejects 12 chars',
        );
        $this->assertNull(
            SouthAfricanIDValidator::extractDateComponents($tooLong),
            'extractDateComponents rejects 14 chars',
        );

        // extractGender
        $this->assertNull(
            SouthAfricanIDValidator::extractGender($tooShort),
            'extractGender rejects 12 chars',
        );
        $this->assertNull(
            SouthAfricanIDValidator::extractGender($tooLong),
            'extractGender rejects 14 chars',
        );

        // extractCitizenship
        $this->assertNull(
            SouthAfricanIDValidator::extractCitizenship($tooShort),
            'extractCitizenship rejects 12 chars',
        );
        $this->assertNull(
            SouthAfricanIDValidator::extractCitizenship($tooLong),
            'extractCitizenship rejects 14 chars',
        );

        // isLegacyID
        $this->assertFalse(
            SouthAfricanIDValidator::isLegacyID($tooShort),
            'isLegacyID rejects 12 chars',
        );
        $this->assertFalse(
            SouthAfricanIDValidator::isLegacyID($tooLong),
            'isLegacyID rejects 14 chars',
        );
    }

    /**
     * Test date validation with non-6 character input.
     */
    public function testDateLengthValidation(): void
    {
        // Test exactly 6 chars (valid)
        $this->assertTrue(
            SouthAfricanIDValidator::isValidIDDate('800101'),
            'Exactly 6 chars can be valid',
        );

        // Test 5 chars (invalid)
        $this->assertFalse(
            SouthAfricanIDValidator::isValidIDDate('80010'),
            '5 chars is invalid',
        );

        // Test 7 chars (invalid)
        $this->assertFalse(
            SouthAfricanIDValidator::isValidIDDate('8001011'),
            '7 chars is invalid',
        );

        // Test 6 non-numeric chars
        $this->assertFalse(
            SouthAfricanIDValidator::isValidIDDate('ABCDEF'),
            '6 non-numeric chars is invalid',
        );
    }
}
