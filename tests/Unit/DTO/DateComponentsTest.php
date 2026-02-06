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

        self::assertSame('80', $dateComponents->year);
        self::assertSame('01', $dateComponents->month);
        self::assertSame('15', $dateComponents->day);
    }

    /**
     * Tests fromArray factory method.
     */
    public function testFromArray(): void
    {
        $data = ['year' => '95', 'month' => '12', 'day' => '25'];
        $dateComponents = DateComponents::fromArray($data);

        self::assertSame('95', $dateComponents->year);
        self::assertSame('12', $dateComponents->month);
        self::assertSame('25', $dateComponents->day);
    }

    /**
     * Tests toArray method.
     */
    public function testToArray(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        $expected = ['year' => '80', 'month' => '01', 'day' => '15'];
        self::assertSame($expected, $dateComponents->toArray());
    }

    /**
     * Tests ArrayAccess offsetExists.
     */
    public function testOffsetExists(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::assertTrue(isset($dateComponents['year']));
        self::assertTrue(isset($dateComponents['month']));
        self::assertTrue(isset($dateComponents['day']));
        self::assertFalse(isset($dateComponents['invalid']));
    }

    /**
     * Tests ArrayAccess offsetGet for valid offsets.
     */
    public function testOffsetGetValid(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::assertSame('80', $dateComponents['year']);
        self::assertSame('01', $dateComponents['month']);
        self::assertSame('15', $dateComponents['day']);
    }

    /**
     * Tests ArrayAccess offsetGet for invalid offset throws exception.
     */
    public function testOffsetGetInvalid(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid offset "invalid". Valid offsets are: year, month, day.');

        /** @phpstan-ignore-next-line */
        $dateComponents['invalid'];
    }

    /**
     * Tests ArrayAccess offsetSet throws exception (immutable).
     */
    public function testOffsetSetThrowsException(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot set "year" to "90": DateComponents is immutable.');

        $dateComponents['year'] = '90';
    }

    /**
     * Tests ArrayAccess offsetUnset throws exception (immutable).
     */
    public function testOffsetUnsetThrowsException(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot unset "year": DateComponents is immutable.');

        unset($dateComponents['year']);
    }


    /**
     * Tests offsetGet with integer offset shows integer in error message.
     */
    public function testOffsetGetWithIntegerOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid offset "123".');

        /** @phpstan-ignore-next-line */
        $dateComponents[123];
    }


    /**
     * Tests offsetGet with boolean offset shows type in error message.
     */
    public function testOffsetGetWithBooleanOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(InvalidArgumentException::class);
        // Boolean true becomes "1" when cast to string
        self::expectExceptionMessage('Invalid offset "1".');

        /** @phpstan-ignore-next-line */
        $dateComponents[true];
    }


    /**
     * Tests offsetGet with array offset shows type in error message.
     */
    public function testOffsetGetWithArrayOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(InvalidArgumentException::class);
        // Non-scalar uses gettype()
        self::expectExceptionMessage('Invalid offset "array".');

        /** @phpstan-ignore-next-line */
        $dateComponents[['invalid']];
    }


    /**
     * Tests offsetSet with integer offset and value shows integers in error message.
     */
    public function testOffsetSetWithIntegerOffsetAndValue(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot set "0" to "99": DateComponents is immutable.');

        $dateComponents[0] = 99;
    }


    /**
     * Tests offsetSet with array value shows type in error message.
     */
    public function testOffsetSetWithArrayValue(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot set "year" to "array": DateComponents is immutable.');

        $dateComponents['year'] = ['invalid'];
    }


    /**
     * Tests offsetUnset with integer offset shows integer in error message.
     */
    public function testOffsetUnsetWithIntegerOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot unset "0": DateComponents is immutable.');

        unset($dateComponents[0]);
    }


    /**
     * Tests offsetUnset with object offset shows type in error message.
     */
    public function testOffsetUnsetWithObjectOffset(): void
    {
        $dateComponents = new DateComponents('80', '01', '15');

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage('Cannot unset "object": DateComponents is immutable.');

        unset($dateComponents[new stdClass()]);
    }
}
