<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\DTO;

use ArrayAccess;
use BadMethodCallException;
use InvalidArgumentException;
use Override;

/**
 * Represents the date components extracted from a South African ID number.
 *
 * Note: The century cannot be definitively determined from the ID alone
 * as it only contains a 2-digit year. Values are stored as strings to
 * preserve leading zeros (e.g., '01' for January, '05' for the 5th).
 *
 * @implements ArrayAccess<string, string>
 */
final readonly class DateComponents implements ArrayAccess
{
    /**
     * Creates a new DateComponents instance.
     *
     * @param string $year  The 2-digit year (00-99).
     * @param string $month The 2-digit month (01-12).
     * @param string $day   The 2-digit day (01-31).
     */
    public function __construct(
        public string $year,
        public string $month,
        public string $day,
    ) {}


    /**
     * Creates a DateComponents instance from an associative array.
     *
     * @param array{year: string, month: string, day: string} $data The date data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            year: $data['year'],
            month: $data['month'],
            day: $data['day'],
        );
    }


    /**
     * Converts the DateComponents to an associative array.
     *
     * @return array{year: string, month: string, day: string}
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
        ];
    }


    /**
     * Checks if an offset exists.
     *
     * @param mixed $offset The offset to check.
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return \in_array($offset, ['year', 'month', 'day'], true);
    }


    /**
     * Gets the value at the specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     *
     *
     * @throws InvalidArgumentException If the offset is invalid.
     */
    #[Override]
    public function offsetGet(mixed $offset): string
    {
        return match ($offset) {
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            default => throw new InvalidArgumentException(
                \sprintf('Invalid offset "%s". Valid offsets are: year, month, day.', \is_scalar($offset) ? (string) $offset : \gettype($offset)),
            ),
        };
    }


    /**
     * Prevents setting values (immutable).
     *
     * @param mixed $offset The offset.
     * @param mixed $value  The value.
     *
     * @throws BadMethodCallException Always, as DateComponents is immutable.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offsetStr = \is_scalar($offset) ? (string) $offset : \gettype($offset);
        $valueStr = \is_scalar($value) ? (string) $value : \gettype($value);

        throw new BadMethodCallException(
            \sprintf('Cannot set "%s" to "%s": DateComponents is immutable.', $offsetStr, $valueStr),
        );
    }


    /**
     * Prevents unsetting values (immutable).
     *
     * @param mixed $offset The offset.
     *
     * @throws BadMethodCallException Always, as DateComponents is immutable.
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException(
            \sprintf('Cannot unset "%s": DateComponents is immutable.', \is_scalar($offset) ? (string) $offset : \gettype($offset)),
        );
    }
}
