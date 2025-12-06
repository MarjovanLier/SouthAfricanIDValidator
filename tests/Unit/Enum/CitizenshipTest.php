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
        $this->assertSame('south_african_citizen', Citizenship::SouthAfricanCitizen->value);
        $this->assertSame('permanent_resident', Citizenship::PermanentResident->value);
        $this->assertSame('refugee', Citizenship::Refugee->value);
    }

    /**
     * Tests fromDigit with valid digits.
     */
    public function testFromDigitValid(): void
    {
        $this->assertSame(Citizenship::SouthAfricanCitizen, Citizenship::fromDigit('0'));
        $this->assertSame(Citizenship::PermanentResident, Citizenship::fromDigit('1'));
        $this->assertSame(Citizenship::Refugee, Citizenship::fromDigit('2'));
    }

    /**
     * Tests fromDigit with invalid digits returns null.
     */
    public function testFromDigitInvalid(): void
    {
        $this->assertNull(Citizenship::fromDigit('3'));
        $this->assertNull(Citizenship::fromDigit('4'));
        $this->assertNull(Citizenship::fromDigit('5'));
        $this->assertNull(Citizenship::fromDigit('6'));
        $this->assertNull(Citizenship::fromDigit('7'));
        $this->assertNull(Citizenship::fromDigit('8'));
        $this->assertNull(Citizenship::fromDigit('9'));
    }

    /**
     * Tests fromDigit with non-digit characters.
     */
    public function testFromDigitNonDigit(): void
    {
        $this->assertNull(Citizenship::fromDigit('a'));
        $this->assertNull(Citizenship::fromDigit(''));
        $this->assertNull(Citizenship::fromDigit('00'));
        $this->assertNull(Citizenship::fromDigit('-1'));
    }
}
