<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\DTO;

use ArrayAccess;
use BadMethodCallException;
use InvalidArgumentException;
use MarjovanLier\SouthAfricanIDValidator\Enum\Citizenship;
use MarjovanLier\SouthAfricanIDValidator\Enum\Gender;
use MarjovanLier\SouthAfricanIDValidator\Enum\RaceIndicator;
use Override;

/**
 * Represents the complete validation result for a South African ID number.
 *
 * This DTO implements ArrayAccess for backwards compatibility with existing
 * code that accesses results using array syntax (e.g., $result['valid']).
 *
 * @implements ArrayAccess<string, bool|array{year: string, month: string, day: string}|string|null>
 */
final readonly class IDValidationResult implements ArrayAccess
{
    /**
     * Creates a new IDValidationResult instance.
     *
     * @param bool                $valid          Whether the ID number is valid.
     * @param DateComponents|null $dateComponents The date components, or null if invalid.
     * @param Gender|null         $gender         The gender, or null if invalid.
     * @param Citizenship|null    $citizenship    The citizenship status, or null if invalid.
     * @param bool                $isLegacy       Whether this is a legacy format ID.
     * @param RaceIndicator|null  $raceIndicator  The race indicator enum, or null if invalid.
     */
    public function __construct(
        public bool $valid,
        public ?DateComponents $dateComponents,
        public ?Gender $gender,
        public ?Citizenship $citizenship,
        public bool $isLegacy,
        public ?RaceIndicator $raceIndicator,
    ) {}


    /**
     * Creates an invalid result with all null values.
     */
    public static function invalid(): self
    {
        return new self(
            valid: false,
            dateComponents: null,
            gender: null,
            citizenship: null,
            isLegacy: false,
            raceIndicator: null,
        );
    }


    /**
     * Converts the result to an associative array (legacy format).
     *
     * This method returns the same structure as the original extractInfo()
     * method for full backwards compatibility.
     *
     * @return array<string, bool|array<string, string>|string|null>
     *
     * @phan-suppress PhanPossiblyUndeclaredProperty Null-safe operator handles nullable enums correctly
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'date_components' => $this->dateComponents?->toArray(),
            'gender' => $this->gender?->value,
            'citizenship' => $this->citizenship?->value,
            'is_legacy' => $this->isLegacy,
            'race_indicator' => $this->raceIndicator?->value,
        ];
    }


    /**
     * Checks if the result represents a valid South African citizen.
     */
    public function isSouthAfricanCitizen(): bool
    {
        return $this->valid && $this->citizenship === Citizenship::SouthAfricanCitizen;
    }


    /**
     * Checks if the result represents a valid male.
     */
    public function isMale(): bool
    {
        return $this->valid && $this->gender === Gender::Male;
    }


    /**
     * Checks if the result represents a valid female.
     */
    public function isFemale(): bool
    {
        return $this->valid && $this->gender === Gender::Female;
    }


    /**
     * Checks if an offset exists.
     *
     * @param mixed $offset The offset to check.
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return \in_array($offset, [
            'valid',
            'date_components',
            'gender',
            'citizenship',
            'is_legacy',
            'race_indicator',
        ], true);
    }


    /**
     * Returns values in legacy array format for backwards compatibility.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return bool|array{year: string, month: string, day: string}|string|null
     *
     * @throws InvalidArgumentException If the offset is invalid.
     *
     * @phan-suppress PhanPossiblyUndeclaredProperty Null-safe operator handles nullable enums correctly
     */
    #[Override]
    public function offsetGet(mixed $offset): bool|array|string|null
    {
        return match ($offset) {
            'valid' => $this->valid,
            'date_components' => $this->dateComponents?->toArray(),
            'gender' => $this->gender?->value,
            'citizenship' => $this->citizenship?->value,
            'is_legacy' => $this->isLegacy,
            'race_indicator' => $this->raceIndicator?->value,
            default => throw new InvalidArgumentException(
                \sprintf('Invalid offset "%s".', \is_scalar($offset) ? (string) $offset : \gettype($offset)),
            ),
        };
    }


    /**
     * Prevents setting values (immutable).
     *
     * @param mixed $offset The offset.
     * @param mixed $value  The value.
     *
     * @throws BadMethodCallException Always, as IDValidationResult is immutable.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offsetStr = \is_scalar($offset) ? (string) $offset : \gettype($offset);
        $valueStr = \is_scalar($value) ? (string) $value : \gettype($value);

        throw new BadMethodCallException(
            \sprintf('Cannot set "%s" to "%s": IDValidationResult is immutable.', $offsetStr, $valueStr),
        );
    }


    /**
     * Prevents unsetting values (immutable).
     *
     * @param mixed $offset The offset.
     *
     * @throws BadMethodCallException Always, as IDValidationResult is immutable.
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException(
            \sprintf('Cannot unset "%s": IDValidationResult is immutable.', \is_scalar($offset) ? (string) $offset : \gettype($offset)),
        );
    }
}
