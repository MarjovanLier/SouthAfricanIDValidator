<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Feature;

use MarjovanLier\SouthAfricanIDValidator\Enum\Citizenship;
use MarjovanLier\SouthAfricanIDValidator\Enum\Gender;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that extractInfo() results are consistent with individual extraction methods.
 *
 * A bug in one extraction path could go undetected if only one method is tested.
 * These tests cross-check that all public methods agree on the same ID input.
 */
#[CoversClass(SouthAfricanIDValidator::class)]
final class ConsistencyValidationTest extends TestCase
{
    /**
     * Tests that extractInfo() agrees with individual methods for a modern male SA citizen.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testModernMaleCitizenConsistency(): void
    {
        // Modern male SA citizen: 8701105800085
        // DOB: 87-01-10, sequence 5800 (male), citizenship 0 (SA citizen), race 8 (modern)
        $idNumber = '8701105800085';

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);
        $dateComponents = SouthAfricanIDValidator::extractDateComponents($idNumber);
        $gender = SouthAfricanIDValidator::extractGender($idNumber);
        $citizenship = SouthAfricanIDValidator::extractCitizenship($idNumber);
        $isLegacy = SouthAfricanIDValidator::isLegacyID($idNumber);

        // extractInfo agrees with luhnIDValidate
        self::assertTrue($idValidationResult->valid, 'extractInfo should mark this ID as valid');
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($idNumber),
            'luhnIDValidate should agree with extractInfo',
        );

        // Gender consistency
        self::assertNotNull($idValidationResult->gender, 'Valid ID must have gender');
        self::assertSame(
            $gender,
            $idValidationResult->gender->value,
            'extractInfo().gender must match extractGender()',
        );
        self::assertTrue($idValidationResult->isMale(), 'isMale() must be true for male sequence');
        self::assertFalse($idValidationResult->isFemale(), 'isFemale() must be false for male sequence');

        // Citizenship consistency
        self::assertNotNull($idValidationResult->citizenship, 'Valid ID must have citizenship');
        self::assertSame(
            $citizenship,
            $idValidationResult->citizenship->value,
            'extractInfo().citizenship must match extractCitizenship()',
        );
        self::assertTrue(
            $idValidationResult->isSouthAfricanCitizen(),
            'isSouthAfricanCitizen() must be true for citizenship digit 0',
        );

        // Date components consistency
        self::assertNotNull($dateComponents, 'extractDateComponents should return data');
        self::assertNotNull($idValidationResult->dateComponents, 'extractInfo should include date components');
        self::assertSame(
            $dateComponents,
            $idValidationResult->dateComponents->toArray(),
            'extractInfo().dateComponents must match extractDateComponents()',
        );

        // Legacy status consistency
        self::assertSame(
            $isLegacy,
            $idValidationResult->isLegacy,
            'extractInfo().isLegacy must match isLegacyID()',
        );
        self::assertFalse($isLegacy, 'Race indicator 8 is modern, not legacy');
    }


    /**
     * Tests consistency for a legacy female permanent resident.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testLegacyFemalePermanentResidentConsistency(): void
    {
        // Legacy female permanent resident: 5001010001108
        // DOB: 50-01-01, seq 0001 (female), cit 1 (perm res), race 0 (legacy White)
        $idNumber = '5001010001108';

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);
        $gender = SouthAfricanIDValidator::extractGender($idNumber);
        $citizenship = SouthAfricanIDValidator::extractCitizenship($idNumber);
        $isLegacy = SouthAfricanIDValidator::isLegacyID($idNumber);

        self::assertTrue($idValidationResult->valid, 'ID must be valid');
        self::assertNotNull($idValidationResult->gender, 'Valid ID must have gender');
        self::assertSame($gender, $idValidationResult->gender->value);
        self::assertTrue($idValidationResult->isFemale(), 'Sequence < 5000 must be female');
        self::assertNotNull($idValidationResult->citizenship, 'Valid ID must have citizenship');
        self::assertSame($citizenship, $idValidationResult->citizenship->value);
        self::assertSame('permanent_resident', $citizenship);
        self::assertFalse($idValidationResult->isSouthAfricanCitizen(), 'Permanent resident is not SA citizen');
        self::assertSame($isLegacy, $idValidationResult->isLegacy);
        self::assertTrue($isLegacy, 'Race indicator 0 is legacy');
    }


    /**
     * Tests consistency for a refugee ID.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testRefugeeConsistency(): void
    {
        // Refugee male: 8001015002280
        // DOB: 80-01-01, seq 5002 (male), cit 2 (refugee), race 8 (modern)
        $idNumber = '8001015002280';

        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);
        $citizenship = SouthAfricanIDValidator::extractCitizenship($idNumber);

        self::assertTrue($idValidationResult->valid, 'ID must be valid');
        self::assertNotNull($idValidationResult->citizenship, 'Valid ID must have citizenship');
        self::assertSame($citizenship, $idValidationResult->citizenship->value);
        self::assertSame('refugee', $citizenship);
        self::assertFalse($idValidationResult->isSouthAfricanCitizen(), 'Refugee is not SA citizen');
        self::assertSame(Citizenship::Refugee, $idValidationResult->citizenship);
    }


    /**
     * Tests that batchValidate matches individual luhnIDValidate calls.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testBatchValidateMatchesIndividual(): void
    {
        $ids = [
            '8701105800085',  // Valid
            '1234567890123',  // Invalid (bad checksum)
            '0000000000000',  // Invalid (invalid date)
            '8001015009087',  // Valid
        ];

        $batchResults = SouthAfricanIDValidator::batchValidate($ids);

        // Verify all IDs are present in results
        self::assertCount(\count($ids), $batchResults, 'Batch should return one result per input');

        // Verify each result matches individual validation
        // PHP converts numeric string keys to integers, so cast back to string
        foreach ($batchResults as $idNumber => $batchResult) {
            $idStr = (string) $idNumber;
            $individual = SouthAfricanIDValidator::luhnIDValidate($idStr);
            self::assertSame(
                $individual,
                $batchResult,
                \sprintf('batchValidate result must match luhnIDValidate for ID %s', $idStr),
            );
        }
    }


    /**
     * Tests that wouldBeDuplicates correctly identifies legacy/modern pairs from convertLegacyToModern.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testLegacyModernPairsAreDuplicates(): void
    {
        // Valid legacy ID: 5001010001108
        $legacyId = '5001010001108';

        $modernId = SouthAfricanIDValidator::convertLegacyToModern($legacyId);

        self::assertNotNull($modernId, 'Conversion should succeed for valid legacy ID');
        self::assertTrue(
            SouthAfricanIDValidator::wouldBeDuplicates($legacyId, $modernId),
            'Legacy and modern versions must share the same first 11 digits',
        );

        // The modern version should also be valid
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($modernId),
            'Converted modern ID must pass validation',
        );
    }


    /**
     * Tests that extractInfo toArray matches individual field access.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testExtractInfoToArrayConsistency(): void
    {
        $idNumber = '8701105800085';
        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertTrue($idValidationResult->valid, 'ID must be valid for this test');
        self::assertNotNull($idValidationResult->gender, 'Valid ID must have gender');
        self::assertNotNull($idValidationResult->citizenship, 'Valid ID must have citizenship');
        self::assertNotNull($idValidationResult->raceIndicator, 'Valid ID must have race indicator');
        self::assertNotNull($idValidationResult->dateComponents, 'Valid ID must have date components');

        // Compare the full array at once to avoid PHPStan offset access issues
        $expected = [
            'valid' => $idValidationResult->valid,
            'date_components' => $idValidationResult->dateComponents->toArray(),
            'gender' => $idValidationResult->gender->value,
            'citizenship' => $idValidationResult->citizenship->value,
            'is_legacy' => $idValidationResult->isLegacy,
            'race_indicator' => $idValidationResult->raceIndicator->value,
        ];

        self::assertSame($expected, $idValidationResult->toArray(), 'toArray() must match DTO properties');
    }


    /**
     * Tests that ArrayAccess on IDValidationResult matches property access.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testArrayAccessConsistencyWithProperties(): void
    {
        $idNumber = '8701105800085';
        $idValidationResult = SouthAfricanIDValidator::extractInfo($idNumber);

        self::assertNotNull($idValidationResult->gender, 'Valid ID must have gender');
        self::assertNotNull($idValidationResult->citizenship, 'Valid ID must have citizenship');
        self::assertNotNull($idValidationResult->raceIndicator, 'Valid ID must have race indicator');

        self::assertSame($idValidationResult->valid, $idValidationResult['valid']);
        self::assertSame($idValidationResult->gender->value, $idValidationResult['gender']);
        self::assertSame($idValidationResult->citizenship->value, $idValidationResult['citizenship']);
        self::assertSame($idValidationResult->isLegacy, $idValidationResult['is_legacy']);
        self::assertSame($idValidationResult->raceIndicator->value, $idValidationResult['race_indicator']);
    }


    /**
     * Tests that an invalid ID returns consistent results across all methods.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testInvalidIdConsistencyAcrossMethods(): void
    {
        $invalidId = '0000000000000';

        $idValidationResult = SouthAfricanIDValidator::extractInfo($invalidId);
        $dateComponents = SouthAfricanIDValidator::extractDateComponents($invalidId);
        $gender = SouthAfricanIDValidator::extractGender($invalidId);
        $citizenship = SouthAfricanIDValidator::extractCitizenship($invalidId);

        self::assertFalse($idValidationResult->valid, 'extractInfo must mark invalid ID as invalid');
        self::assertNull($idValidationResult->dateComponents, 'Invalid ID should have no date components in extractInfo');
        self::assertNull($idValidationResult->gender, 'Invalid ID should have no gender in extractInfo');
        self::assertNull($idValidationResult->citizenship, 'Invalid ID should have no citizenship in extractInfo');
        self::assertNull($dateComponents, 'extractDateComponents should return null for invalid date');
        self::assertSame('female', $gender, 'extractGender only checks length, not validity');
        self::assertSame('south_african_citizen', $citizenship, 'extractCitizenship only checks length and digit');
    }


    /**
     * Tests that the DTO gender enum matches the string method.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testGenderEnumMatchesStringMethod(): void
    {
        // Female: 8701104000083
        $femaleId = '8701104000083';

        $idValidationResult = SouthAfricanIDValidator::extractInfo($femaleId);
        self::assertTrue($idValidationResult->valid, 'Female ID must be valid');
        self::assertSame(Gender::Female, $idValidationResult->gender);
        self::assertNotNull($idValidationResult->gender, 'Valid ID must have gender');
        self::assertSame('female', $idValidationResult->gender->value);
        self::assertSame('female', SouthAfricanIDValidator::extractGender($femaleId));

        // Male: 8701105800085
        $maleId = '8701105800085';
        $infoMale = SouthAfricanIDValidator::extractInfo($maleId);
        self::assertSame(Gender::Male, $infoMale->gender);
        self::assertNotNull($infoMale->gender, 'Valid ID must have gender');
        self::assertSame('male', $infoMale->gender->value);
        self::assertSame('male', SouthAfricanIDValidator::extractGender($maleId));
    }
}
