<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::luhnIDValidate
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class LuhnIDValidateTest extends TestCase
{
    /**
     * @var string
     */
    private const ALTERED_ID_NUMBER = '8701105022186';


    /**
     * Provides a set of valid ID numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'8701105800085'}, list{'3202295029085'}, list{'4806010046080'}, list{'3206015052087'},
     *     list{'0809015019080'}}
     */
    public static function provideValidIDNumbers(): array
    {
        return [
            ['8701105800085'],
            ['3202295029085'],
            ['4806010046080'],
            ['3206015052087'],
            ['0809015019080'],
        ];
    }


    /**
     * Provides a set of invalid ID numbers.
     *
     * @return array<string[]>
     *
     * @psalm-return list{list{'1234567890129'}, list{'9876543210186'}}
     */
    public static function provideInvalidIDNumbers(): array
    {
        return [
            ['1234567890129'],
            ['9876543210186'],
        ];
    }


    /**
     * Provides a set of invalid ID numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'0000000000380'}, list{'0000000000480'}, list{'0000000000580'}, list{'0000000000680'},
     *     list{'0000000000780'}, list{'0000000000880'}, list{'0000000000980'}, list{'0000000000380'},
     *     list{'0000000000480'}, list{'0000000000580'}, list{'0000000000680'}, list{'0000000000780'},
     *     list{'0000000000880'}, list{'0000000000980'}}
     */
    public static function provideInvalidFormatIDNumbers(): array
    {
        return [
            ['0000000000380'],
            ['0000000000480'],
            ['0000000000580'],
            ['0000000000680'],
            ['0000000000780'],
            ['0000000000880'],
            ['0000000000980'],
            ['0000000000380'],
            ['0000000000480'],
            ['0000000000580'],
            ['0000000000680'],
            ['0000000000780'],
            ['0000000000880'],
            ['0000000000980'],
        ];
    }


    /**
     * Tests if valid ID numbers are correctly validated.
     *
     * @param string $idNumber The ID number to validate.
     *
     * @dataProvider provideValidIDNumbers
     */
    public function testValidIDNumbers(string $idNumber): void
    {
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * @dataProvider provideInvalidIDNumbers
     */
    public function testInvalidIDNumbers(string $idNumber): void
    {
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * @dataProvider provideInvalidFormatIDNumbers
     */
    public function testInvalidFormatIDNumbers(string $idNumber): void
    {
        self::assertNull(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests if non-numeric values at the 11th position are correctly invalidated.
     */
    public function testNonNumericEleventhCharacter(): void
    {
        // X at the 11th position
        $idNumber = '8701105022X86';
        // As per your existing logic, this should return false
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests Luhn algorithm parity and digit logic.
     */
    public function testParityAndDigitLogic(): void
    {
        $idNumber = '8701105025086';
        // Here the 10th digit is 5, which when doubled gives 10.
        // This assumes you have a helper function to compute Luhn's checksum without the specific ID logic.
        $expected = $this->computeLuhnChecksum($idNumber);

        self::assertEquals($expected, SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests if string number inputs are correctly validated.
     */
    public function testStringNumberInput(): void
    {
        $idNumber = '8701105800085';
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests if non-integer values in the ID number are correctly invalidated.
     */
    public function testNonIntegerValues(): void
    {
        $idNumber = '87011X5022086';
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests the parity logic of the Luhn algorithm.
     */
    public function testParityLogic(): void
    {
        $idNumber = '8701105800085';
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));

        $idNumberAltered = '8701105022186';
        // Try to change a number that would affect parity calculation.
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumberAltered));
    }


    /**
     * Tests if ID numbers containing the digit five are correctly validated.
     */
    public function testNumberHavingFive(): void
    {
        $idNumber = '8701155022086';
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests if the Luhn checksum logic works correctly when digits are incremented or decremented.
     */
    public function testTotalIncrementDecrementLogic(): void
    {
        $idNumber = '8701105800085';
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate($idNumber));

        $idNumberAltered = '8701105022087';
        // Altering the last digit should make the ID invalid.
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumberAltered));
    }


    /**
     * Tests the parity logic of the Luhn algorithm.
     */
    public function testLuhnParityLogic(): void
    {
        $idNumber = '8701105025086';
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests if the Luhn algorithm correctly splits double digits.
     */
    public function testLuhnDoubleDigitSplit(): void
    {
        $idNumber = '8701105026086';
        // The 10th character when doubled becomes 12, which should be treated as 1 + 2
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate($idNumber));
    }


    /**
     * Tests if altering a digit in the ID number makes it invalid.
     */
    public function testAlterationMakesIDInvalid(): void
    {
        // Altering a single digit to make it invalid
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate(self::ALTERED_ID_NUMBER));
    }


    public function testLuhnIDValidateWithMod5True(): void
    {
        self::assertTrue(SouthAfricanIDValidator::luhnIDValidate('8809260027087'));
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate('6804045679085'));
        self::assertFalse(SouthAfricanIDValidator::luhnIDValidate('8806087993185'));
    }


    private function computeLuhnChecksum(string $number): bool
    {
        $parity = (strlen($number) % 2);
        $total = 0;

        foreach (str_split($number) as $key => $digit) {
            $digit = (int) $digit;

            if (($key % 2) === $parity) {
                $digit *= 2;
            }

            if ($digit >= 10) {
                // Equivalent to splitting the digits and summing them.
                $digit -= 9;
            }

            $total += $digit;
        }

        return ($total % 10) === 0;
    }
}
