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
        self::assertSame('white', RaceIndicator::White->value);
        self::assertSame('cape_coloured', RaceIndicator::CapeColoured->value);
        self::assertSame('malay', RaceIndicator::Malay->value);
        self::assertSame('griqua', RaceIndicator::Griqua->value);
        self::assertSame('chinese', RaceIndicator::Chinese->value);
        self::assertSame('indian', RaceIndicator::Indian->value);
        self::assertSame('other_asian', RaceIndicator::OtherAsian->value);
        self::assertSame('other_coloured', RaceIndicator::OtherColoured->value);
        self::assertSame('unspecified', RaceIndicator::Unspecified->value);
        self::assertSame('unknown', RaceIndicator::Unknown->value);
    }

    /**
     * Tests fromDigit for all valid legacy apartheid-era digits (0-7).
     */
    public function testFromDigitLegacy(): void
    {
        self::assertSame(RaceIndicator::White, RaceIndicator::fromDigit('0'));
        self::assertSame(RaceIndicator::CapeColoured, RaceIndicator::fromDigit('1'));
        self::assertSame(RaceIndicator::Malay, RaceIndicator::fromDigit('2'));
        self::assertSame(RaceIndicator::Griqua, RaceIndicator::fromDigit('3'));
        self::assertSame(RaceIndicator::Chinese, RaceIndicator::fromDigit('4'));
        self::assertSame(RaceIndicator::Indian, RaceIndicator::fromDigit('5'));
        self::assertSame(RaceIndicator::OtherAsian, RaceIndicator::fromDigit('6'));
        self::assertSame(RaceIndicator::OtherColoured, RaceIndicator::fromDigit('7'));
    }

    /**
     * Tests fromDigit for modern digits (8-9).
     */
    public function testFromDigitModern(): void
    {
        self::assertSame(RaceIndicator::Unspecified, RaceIndicator::fromDigit('8'));
        self::assertSame(RaceIndicator::Unknown, RaceIndicator::fromDigit('9'));
    }

    /**
     * Tests fromDigit with invalid characters returns null.
     */
    public function testFromDigitInvalid(): void
    {
        self::assertNull(RaceIndicator::fromDigit('a'));
        self::assertNull(RaceIndicator::fromDigit(''));
        self::assertNull(RaceIndicator::fromDigit('10'));
        self::assertNull(RaceIndicator::fromDigit('-1'));
    }

    /**
     * Tests the description method for all cases.
     */
    public function testDescription(): void
    {
        self::assertSame('White', RaceIndicator::White->description());
        self::assertSame('Cape Coloured', RaceIndicator::CapeColoured->description());
        self::assertSame('Malay', RaceIndicator::Malay->description());
        self::assertSame('Griqua', RaceIndicator::Griqua->description());
        self::assertSame('Chinese', RaceIndicator::Chinese->description());
        self::assertSame('Indian', RaceIndicator::Indian->description());
        self::assertSame('Other Asian', RaceIndicator::OtherAsian->description());
        self::assertSame('Other Coloured', RaceIndicator::OtherColoured->description());
        self::assertSame('Unspecified (post-1994)', RaceIndicator::Unspecified->description());
        self::assertSame('Unknown/Not documented', RaceIndicator::Unknown->description());
    }

    /**
     * Tests isLegacy for legacy indicators (0-7).
     */
    public function testIsLegacyTrue(): void
    {
        self::assertTrue(RaceIndicator::White->isLegacy());
        self::assertTrue(RaceIndicator::CapeColoured->isLegacy());
        self::assertTrue(RaceIndicator::Malay->isLegacy());
        self::assertTrue(RaceIndicator::Griqua->isLegacy());
        self::assertTrue(RaceIndicator::Chinese->isLegacy());
        self::assertTrue(RaceIndicator::Indian->isLegacy());
        self::assertTrue(RaceIndicator::OtherAsian->isLegacy());
        self::assertTrue(RaceIndicator::OtherColoured->isLegacy());
    }

    /**
     * Tests isLegacy for modern indicators (8-9).
     */
    public function testIsLegacyFalse(): void
    {
        self::assertFalse(RaceIndicator::Unspecified->isLegacy());
        self::assertFalse(RaceIndicator::Unknown->isLegacy());
    }
}
