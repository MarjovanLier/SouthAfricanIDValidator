<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit\Enum;

use MarjovanLier\SouthAfricanIDValidator\Enum\Citizenship;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Citizenship enum.
 */
final class CitizenshipTest extends TestCase
{
    /**
     * Tests that the enum has the correct values.
     */
    public function testEnumValues(): void
    {
        self::assertSame('south_african_citizen', Citizenship::SouthAfricanCitizen->value);
        self::assertSame('permanent_resident', Citizenship::PermanentResident->value);
        self::assertSame('refugee', Citizenship::Refugee->value);
    }

    /**
     * Tests fromDigit with valid digits.
     */
    public function testFromDigitValid(): void
    {
        self::assertSame(Citizenship::SouthAfricanCitizen, Citizenship::fromDigit('0'));
        self::assertSame(Citizenship::PermanentResident, Citizenship::fromDigit('1'));
        self::assertSame(Citizenship::Refugee, Citizenship::fromDigit('2'));
    }

    /**
     * Tests fromDigit with invalid digits returns null.
     */
    public function testFromDigitInvalid(): void
    {
        self::assertNull(Citizenship::fromDigit('3'));
        self::assertNull(Citizenship::fromDigit('4'));
        self::assertNull(Citizenship::fromDigit('5'));
        self::assertNull(Citizenship::fromDigit('6'));
        self::assertNull(Citizenship::fromDigit('7'));
        self::assertNull(Citizenship::fromDigit('8'));
        self::assertNull(Citizenship::fromDigit('9'));
    }

    /**
     * Tests fromDigit with non-digit characters.
     */
    public function testFromDigitNonDigit(): void
    {
        self::assertNull(Citizenship::fromDigit('a'));
        self::assertNull(Citizenship::fromDigit(''));
        self::assertNull(Citizenship::fromDigit('00'));
        self::assertNull(Citizenship::fromDigit('-1'));
    }
}
