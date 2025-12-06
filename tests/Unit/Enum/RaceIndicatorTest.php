<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit\Enum;

use MarjovanLier\SouthAfricanIDValidator\Enum\RaceIndicator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the RaceIndicator enum.
 */
final class RaceIndicatorTest extends TestCase
{
    /**
     * Tests that all enum cases have the correct values.
     */
    public function testEnumValues(): void
    {
        $this->assertSame('white', RaceIndicator::White->value);
        $this->assertSame('cape_coloured', RaceIndicator::CapeColoured->value);
        $this->assertSame('malay', RaceIndicator::Malay->value);
        $this->assertSame('griqua', RaceIndicator::Griqua->value);
        $this->assertSame('chinese', RaceIndicator::Chinese->value);
        $this->assertSame('indian', RaceIndicator::Indian->value);
        $this->assertSame('other_asian', RaceIndicator::OtherAsian->value);
        $this->assertSame('other_coloured', RaceIndicator::OtherColoured->value);
        $this->assertSame('unspecified', RaceIndicator::Unspecified->value);
        $this->assertSame('unknown', RaceIndicator::Unknown->value);
    }

    /**
     * Tests fromDigit for all valid legacy apartheid-era digits (0-7).
     */
    public function testFromDigitLegacy(): void
    {
        $this->assertSame(RaceIndicator::White, RaceIndicator::fromDigit('0'));
        $this->assertSame(RaceIndicator::CapeColoured, RaceIndicator::fromDigit('1'));
        $this->assertSame(RaceIndicator::Malay, RaceIndicator::fromDigit('2'));
        $this->assertSame(RaceIndicator::Griqua, RaceIndicator::fromDigit('3'));
        $this->assertSame(RaceIndicator::Chinese, RaceIndicator::fromDigit('4'));
        $this->assertSame(RaceIndicator::Indian, RaceIndicator::fromDigit('5'));
        $this->assertSame(RaceIndicator::OtherAsian, RaceIndicator::fromDigit('6'));
        $this->assertSame(RaceIndicator::OtherColoured, RaceIndicator::fromDigit('7'));
    }

    /**
     * Tests fromDigit for modern digits (8-9).
     */
    public function testFromDigitModern(): void
    {
        $this->assertSame(RaceIndicator::Unspecified, RaceIndicator::fromDigit('8'));
        $this->assertSame(RaceIndicator::Unknown, RaceIndicator::fromDigit('9'));
    }

    /**
     * Tests fromDigit with invalid characters returns null.
     */
    public function testFromDigitInvalid(): void
    {
        $this->assertNull(RaceIndicator::fromDigit('a'));
        $this->assertNull(RaceIndicator::fromDigit(''));
        $this->assertNull(RaceIndicator::fromDigit('10'));
        $this->assertNull(RaceIndicator::fromDigit('-1'));
    }

    /**
     * Tests the description method for all cases.
     */
    public function testDescription(): void
    {
        $this->assertSame('White', RaceIndicator::White->description());
        $this->assertSame('Cape Coloured', RaceIndicator::CapeColoured->description());
        $this->assertSame('Malay', RaceIndicator::Malay->description());
        $this->assertSame('Griqua', RaceIndicator::Griqua->description());
        $this->assertSame('Chinese', RaceIndicator::Chinese->description());
        $this->assertSame('Indian', RaceIndicator::Indian->description());
        $this->assertSame('Other Asian', RaceIndicator::OtherAsian->description());
        $this->assertSame('Other Coloured', RaceIndicator::OtherColoured->description());
        $this->assertSame('Unspecified (post-1994)', RaceIndicator::Unspecified->description());
        $this->assertSame('Unknown/Not documented', RaceIndicator::Unknown->description());
    }

    /**
     * Tests isLegacy for legacy indicators (0-7).
     */
    public function testIsLegacyTrue(): void
    {
        $this->assertTrue(RaceIndicator::White->isLegacy());
        $this->assertTrue(RaceIndicator::CapeColoured->isLegacy());
        $this->assertTrue(RaceIndicator::Malay->isLegacy());
        $this->assertTrue(RaceIndicator::Griqua->isLegacy());
        $this->assertTrue(RaceIndicator::Chinese->isLegacy());
        $this->assertTrue(RaceIndicator::Indian->isLegacy());
        $this->assertTrue(RaceIndicator::OtherAsian->isLegacy());
        $this->assertTrue(RaceIndicator::OtherColoured->isLegacy());
    }

    /**
     * Tests isLegacy for modern indicators (8-9).
     */
    public function testIsLegacyFalse(): void
    {
        $this->assertFalse(RaceIndicator::Unspecified->isLegacy());
        $this->assertFalse(RaceIndicator::Unknown->isLegacy());
    }
}
