<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit\DTO;

use BadMethodCallException;
use InvalidArgumentException;
use MarjovanLier\SouthAfricanIDValidator\DTO\DateComponents;
use MarjovanLier\SouthAfricanIDValidator\DTO\IDValidationResult;
use MarjovanLier\SouthAfricanIDValidator\Enum\Citizenship;
use MarjovanLier\SouthAfricanIDValidator\Enum\Gender;
use MarjovanLier\SouthAfricanIDValidator\Enum\RaceIndicator;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Unit tests for the IDValidationResult DTO.
 */
final class IDValidationResultTest extends TestCase
{
    /**
     * Tests constructor and property access for a valid result.
     */
    public function testConstructorWithValidResult(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');
        $idValidationResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );

        self::assertTrue($idValidationResult->valid);
        self::assertSame($dateComponents, $idValidationResult->dateComponents);
        self::assertSame(Gender::Male, $idValidationResult->gender);
        self::assertSame(Citizenship::SouthAfricanCitizen, $idValidationResult->citizenship);
        self::assertFalse($idValidationResult->isLegacy);
        self::assertSame(RaceIndicator::Unspecified, $idValidationResult->raceIndicator);
    }

    /**
     * Tests the invalid() factory method.
     */
    public function testInvalidFactory(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::assertFalse($idValidationResult->valid);
        self::assertNull($idValidationResult->dateComponents);
        self::assertNull($idValidationResult->gender);
        self::assertNull($idValidationResult->citizenship);
        self::assertFalse($idValidationResult->isLegacy);
        self::assertNull($idValidationResult->raceIndicator);
    }

    /**
     * Tests toArray method returns correct legacy format.
     */
    public function testToArray(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');
        $idValidationResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Female,
            citizenship: Citizenship::PermanentResident,
            isLegacy: true,
            raceIndicator: RaceIndicator::White,
        );

        $expected = [
            'valid' => true,
            'date_components' => ['year' => '80', 'month' => '01', 'day' => '15'],
            'gender' => 'female',
            'citizenship' => 'permanent_resident',
            'is_legacy' => true,
            'race_indicator' => 'white',
        ];

        self::assertSame($expected, $idValidationResult->toArray());
    }

    /**
     * Tests toArray with null values.
     */
    public function testToArrayWithNulls(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        $expected = [
            'valid' => false,
            'date_components' => null,
            'gender' => null,
            'citizenship' => null,
            'is_legacy' => false,
            'race_indicator' => null,
        ];

        self::assertSame($expected, $idValidationResult->toArray());
    }

    /**
     * Tests isSouthAfricanCitizen helper.
     */
    public function testIsSouthAfricanCitizen(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        // Valid SA citizen
        $citizenResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertTrue($citizenResult->isSouthAfricanCitizen());

        // Valid but not SA citizen (permanent resident)
        $residentResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::PermanentResident,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertFalse($residentResult->isSouthAfricanCitizen());

        // Invalid result
        $invalidResult = IDValidationResult::invalid();
        self::assertFalse($invalidResult->isSouthAfricanCitizen());
    }

    /**
     * Tests isMale helper.
     */
    public function testIsMale(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        // Valid male
        $maleResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertTrue($maleResult->isMale());

        // Valid female
        $femaleResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Female,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertFalse($femaleResult->isMale());

        // Invalid result
        $invalidResult = IDValidationResult::invalid();
        self::assertFalse($invalidResult->isMale());
    }

    /**
     * Tests isFemale helper.
     */
    public function testIsFemale(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        // Valid female
        $femaleResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Female,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertTrue($femaleResult->isFemale());

        // Valid male
        $maleResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertFalse($maleResult->isFemale());

        // Invalid result
        $invalidResult = IDValidationResult::invalid();
        self::assertFalse($invalidResult->isFemale());
    }

    /**
     * Tests ArrayAccess offsetExists.
     */
    public function testOffsetExists(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::assertTrue(isset($idValidationResult['valid']));
        self::assertTrue(isset($idValidationResult['date_components']));
        self::assertTrue(isset($idValidationResult['gender']));
        self::assertTrue(isset($idValidationResult['citizenship']));
        self::assertTrue(isset($idValidationResult['is_legacy']));
        self::assertTrue(isset($idValidationResult['race_indicator']));
        self::assertFalse(isset($idValidationResult['invalid_key']));
    }

    /**
     * Tests ArrayAccess offsetGet for valid offsets.
     */
    public function testOffsetGetValid(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');
        $idValidationResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::Refugee,
            isLegacy: true,
            raceIndicator: RaceIndicator::Indian,
        );

        // ArrayAccess returns legacy-compatible values (strings, not enums)
        self::assertTrue($idValidationResult['valid']);
        self::assertSame(['year' => '80', 'month' => '01', 'day' => '15'], $idValidationResult['date_components']);
        self::assertSame('male', $idValidationResult['gender']);
        self::assertSame('refugee', $idValidationResult['citizenship']);
        self::assertTrue($idValidationResult['is_legacy']);
        self::assertSame('indian', $idValidationResult['race_indicator']);
    }

    /**
     * Tests ArrayAccess offsetGet for invalid offset throws exception.
     */
    public function testOffsetGetInvalid(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid offset "invalid_key".');

        /** @phpstan-ignore-next-line */
        $idValidationResult['invalid_key'];
    }

    /**
     * Tests ArrayAccess offsetSet throws exception (immutable).
     */
    public function testOffsetSetThrowsException(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot set "valid" to "1": IDValidationResult is immutable.');

        $idValidationResult['valid'] = true;
    }

    /**
     * Tests ArrayAccess offsetUnset throws exception (immutable).
     */
    public function testOffsetUnsetThrowsException(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot unset "valid": IDValidationResult is immutable.');

        unset($idValidationResult['valid']);
    }

    /**
     * Tests backwards compatibility with existing code patterns.
     */
    public function testBackwardsCompatibility(): void
    {
        $dateComponents = new DateComponents('80', '01', '01');
        $idValidationResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );

        // These patterns should work exactly as before
        self::assertTrue($idValidationResult['valid'], 'ID should be valid');
        self::assertIsArray($idValidationResult['date_components'], 'Date components should be an array');
        self::assertSame('80', $idValidationResult['date_components']['year'], 'Year should be 80');
        self::assertSame('male', $idValidationResult['gender'], 'Gender should be male');
        self::assertSame('south_african_citizen', $idValidationResult['citizenship'], 'Should be SA citizen');
        self::assertFalse($idValidationResult['is_legacy'], 'Should not be legacy format');
    }


    /**
     * Tests offsetGet with integer offset shows integer in error message.
     */
    public function testOffsetGetWithIntegerOffset(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid offset "123".');

        /** @phpstan-ignore-next-line */
        $idValidationResult[123];
    }


    /**
     * Tests offsetGet with boolean offset shows value in error message.
     */
    public function testOffsetGetWithBooleanOffset(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(InvalidArgumentException::class);
        // Boolean true becomes "1" when cast to string
        self::expectExceptionMessage('Invalid offset "1".');

        /** @phpstan-ignore-next-line */
        $idValidationResult[true];
    }


    /**
     * Tests offsetGet with array offset shows type in error message.
     */
    public function testOffsetGetWithArrayOffset(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(InvalidArgumentException::class);
        // Non-scalar uses gettype()
        self::expectExceptionMessage('Invalid offset "array".');

        /** @phpstan-ignore-next-line */
        $idValidationResult[['test']];
    }


    /**
     * Tests offsetSet with integer offset and value shows integers in error message.
     */
    public function testOffsetSetWithIntegerOffsetAndValue(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot set "0" to "99": IDValidationResult is immutable.');

        $idValidationResult[0] = 99;
    }


    /**
     * Tests offsetSet with array value shows type in error message.
     */
    public function testOffsetSetWithArrayValue(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot set "valid" to "array": IDValidationResult is immutable.');

        $idValidationResult['valid'] = ['invalid'];
    }


    /**
     * Tests offsetUnset with integer offset shows integer in error message.
     */
    public function testOffsetUnsetWithIntegerOffset(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot unset "0": IDValidationResult is immutable.');

        unset($idValidationResult[0]);
    }


    /**
     * Tests offsetUnset with object offset shows type in error message.
     */
    public function testOffsetUnsetWithObjectOffset(): void
    {
        $idValidationResult = IDValidationResult::invalid();

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot unset "object": IDValidationResult is immutable.');

        unset($idValidationResult[new stdClass()]);
    }


    /**
     * Tests isSouthAfricanCitizen with refugee citizenship returns false.
     */
    public function testIsSouthAfricanCitizenWithRefugee(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');
        $idValidationResult = new IDValidationResult(
            valid: true,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::Refugee,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );

        self::assertFalse($idValidationResult->isSouthAfricanCitizen());
    }


    /**
     * Tests isMale and isFemale return false when valid is false even with gender set.
     */
    public function testGenderHelpersRequireValidTrue(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        // Invalid result with Male gender should still return false for isMale
        $invalidMale = new IDValidationResult(
            valid: false,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertFalse($invalidMale->isMale());
        self::assertFalse($invalidMale->isFemale());

        // Invalid result with Female gender should still return false for isFemale
        $invalidFemale = new IDValidationResult(
            valid: false,
            dateComponents: $dateComponents,
            gender: Gender::Female,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );
        self::assertFalse($invalidFemale->isFemale());
        self::assertFalse($invalidFemale->isMale());
    }


    /**
     * Tests isSouthAfricanCitizen returns false when valid is false even with citizenship set.
     */
    public function testIsSouthAfricanCitizenRequiresValidTrue(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $idValidationResult = new IDValidationResult(
            valid: false,
            dateComponents: $dateComponents,
            gender: Gender::Male,
            citizenship: Citizenship::SouthAfricanCitizen,
            isLegacy: false,
            raceIndicator: RaceIndicator::Unspecified,
        );

        self::assertFalse($idValidationResult->isSouthAfricanCitizen());
    }
}
