<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit\DTO;

use BadMethodCallException;
use InvalidArgumentException;
use MarjovanLier\SouthAfricanIDValidator\DTO\DateComponents;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Unit tests for the DateComponents DTO.
 */
final class DateComponentsTest extends TestCase
{
    /**
     * Tests constructor and property access.
     */
    public function testConstructorAndProperties(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->assertSame('80', $dateComponents->year);
        $this->assertSame('01', $dateComponents->month);
        $this->assertSame('15', $dateComponents->day);
    }

    /**
     * Tests fromArray factory method.
     */
    public function testFromArray(): void
    {
        $data = ['year' => '95', 'month' => '12', 'day' => '25'];
        $dateComponents = DateComponents::fromArray($data);

        $this->assertSame('95', $dateComponents->year);
        $this->assertSame('12', $dateComponents->month);
        $this->assertSame('25', $dateComponents->day);
    }

    /**
     * Tests toArray method.
     */
    public function testToArray(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $expected = ['year' => '80', 'month' => '01', 'day' => '15'];
        $this->assertSame($expected, $dateComponents->toArray());
    }

    /**
     * Tests ArrayAccess offsetExists.
     */
    public function testOffsetExists(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->assertTrue(isset($dateComponents['year']));
        $this->assertTrue(isset($dateComponents['month']));
        $this->assertTrue(isset($dateComponents['day']));
        $this->assertFalse(isset($dateComponents['invalid']));
    }

    /**
     * Tests ArrayAccess offsetGet for valid offsets.
     */
    public function testOffsetGetValid(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->assertSame('80', $dateComponents['year']);
        $this->assertSame('01', $dateComponents['month']);
        $this->assertSame('15', $dateComponents['day']);
    }

    /**
     * Tests ArrayAccess offsetGet for invalid offset throws exception.
     */
    public function testOffsetGetInvalid(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid offset "invalid". Valid offsets are: year, month, day.');

        /** @phpstan-ignore-next-line */
        $dateComponents['invalid'];
    }

    /**
     * Tests ArrayAccess offsetSet throws exception (immutable).
     */
    public function testOffsetSetThrowsException(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot set "year" to "90": DateComponents is immutable.');

        $dateComponents['year'] = '90';
    }

    /**
     * Tests ArrayAccess offsetUnset throws exception (immutable).
     */
    public function testOffsetUnsetThrowsException(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot unset "year": DateComponents is immutable.');

        unset($dateComponents['year']);
    }


    /**
     * Tests offsetGet with integer offset shows integer in error message.
     */
    public function testOffsetGetWithIntegerOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid offset "123".');

        /** @phpstan-ignore-next-line */
        $dateComponents[123];
    }


    /**
     * Tests offsetGet with boolean offset shows type in error message.
     */
    public function testOffsetGetWithBooleanOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(InvalidArgumentException::class);
        // Boolean true becomes "1" when cast to string
        $this->expectExceptionMessage('Invalid offset "1".');

        /** @phpstan-ignore-next-line */
        $dateComponents[true];
    }


    /**
     * Tests offsetGet with array offset shows type in error message.
     */
    public function testOffsetGetWithArrayOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(InvalidArgumentException::class);
        // Non-scalar uses gettype()
        $this->expectExceptionMessage('Invalid offset "array".');

        /** @phpstan-ignore-next-line */
        $dateComponents[['invalid']];
    }


    /**
     * Tests offsetSet with integer offset and value shows integers in error message.
     */
    public function testOffsetSetWithIntegerOffsetAndValue(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot set "0" to "99": DateComponents is immutable.');

        /** @phpstan-ignore-next-line */
        $dateComponents[0] = 99;
    }


    /**
     * Tests offsetSet with array value shows type in error message.
     */
    public function testOffsetSetWithArrayValue(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot set "year" to "array": DateComponents is immutable.');

        /** @phpstan-ignore-next-line */
        $dateComponents['year'] = ['invalid'];
    }


    /**
     * Tests offsetUnset with integer offset shows integer in error message.
     */
    public function testOffsetUnsetWithIntegerOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot unset "0": DateComponents is immutable.');

        /** @phpstan-ignore-next-line */
        unset($dateComponents[0]);
    }


    /**
     * Tests offsetUnset with object offset shows type in error message.
     */
    public function testOffsetUnsetWithObjectOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot unset "object": DateComponents is immutable.');

        /** @phpstan-ignore-next-line */
        unset($dateComponents[new stdClass()]);
    }
}
