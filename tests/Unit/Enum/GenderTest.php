<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit\Enum;

use MarjovanLier\SouthAfricanIDValidator\Enum\Gender;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Gender enum.
 */
final class GenderTest extends TestCase
{
    /**
     * Tests that the enum has the correct values.
     */
    public function testEnumValues(): void
    {
        $this->assertSame('female', Gender::Female->value);
        $this->assertSame('male', Gender::Male->value);
    }

    /**
     * Tests fromSequenceNumber with female range (0-4999).
     */
    public function testFromSequenceNumberFemale(): void
    {
        // Boundary: minimum female value
        $this->assertSame(Gender::Female, Gender::fromSequenceNumber(0));

        // Middle of female range
        $this->assertSame(Gender::Female, Gender::fromSequenceNumber(2500));

        // Boundary: maximum female value
        $this->assertSame(Gender::Female, Gender::fromSequenceNumber(4999));
    }

    /**
     * Tests fromSequenceNumber with male range (5000-9999).
     */
    public function testFromSequenceNumberMale(): void
    {
        // Boundary: minimum male value
        $this->assertSame(Gender::Male, Gender::fromSequenceNumber(5000));

        // Middle of male range
        $this->assertSame(Gender::Male, Gender::fromSequenceNumber(7500));

        // Boundary: maximum male value
        $this->assertSame(Gender::Male, Gender::fromSequenceNumber(9999));
    }

    /**
     * Tests the boundary between female and male.
     */
    public function testFromSequenceNumberBoundary(): void
    {
        // Just below the boundary (female)
        $this->assertSame(Gender::Female, Gender::fromSequenceNumber(4999));

        // At the boundary (male)
        $this->assertSame(Gender::Male, Gender::fromSequenceNumber(5000));
    }
}
