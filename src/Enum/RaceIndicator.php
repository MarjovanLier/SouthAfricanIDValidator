<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Enum;

/**
 * Represents the race indicator as encoded in South African ID numbers.
 *
 * This is a legacy classification system from the apartheid era (pre-1994).
 * The 12th digit of the ID number was used to classify individuals by race.
 * This system was officially discontinued after 1994, with modern IDs
 * typically using 8 or 9 in this position.
 *
 * Historical context: These classifications were part of the Population
 * Registration Act of 1950, which was repealed in 1991. The categories
 * reflect the racial terminology used during that period.
 */
enum RaceIndicator: string
{
    case White = 'white';
    case CapeColoured = 'cape_coloured';
    case Malay = 'malay';
    case Griqua = 'griqua';
    case Chinese = 'chinese';
    case Indian = 'indian';
    case OtherAsian = 'other_asian';
    case OtherColoured = 'other_coloured';
    case Unspecified = 'unspecified';
    case Unknown = 'unknown';


    /**
     * Creates a RaceIndicator enum from the race digit.
     *
     * @param string $digit The race indicator digit (0-9).
     *
     * @return self|null Null if the digit is not a single digit character.
     */
    public static function fromDigit(string $digit): ?self
    {
        return match ($digit) {
            '0' => self::White,
            '1' => self::CapeColoured,
            '2' => self::Malay,
            '3' => self::Griqua,
            '4' => self::Chinese,
            '5' => self::Indian,
            '6' => self::OtherAsian,
            '7' => self::OtherColoured,
            '8' => self::Unspecified,
            '9' => self::Unknown,
            default => null,
        };
    }


    /**
     * Returns a human-readable description of the race indicator.
     */
    public function description(): string
    {
        return match ($this) {
            self::White => 'White',
            self::CapeColoured => 'Cape Coloured',
            self::Malay => 'Malay',
            self::Griqua => 'Griqua',
            self::Chinese => 'Chinese',
            self::Indian => 'Indian',
            self::OtherAsian => 'Other Asian',
            self::OtherColoured => 'Other Coloured',
            self::Unspecified => 'Unspecified (post-1994)',
            default => 'Unknown/Not documented',
        };
    }


    /**
     * Checks if this is a legacy (apartheid-era) race indicator.
     *
     * @return bool True if this is a legacy indicator (0-7), false for modern (8-9).
     */
    public function isLegacy(): bool
    {
        return match ($this) {
            self::White,
            self::CapeColoured,
            self::Malay,
            self::Griqua,
            self::Chinese,
            self::Indian,
            self::OtherAsian,
            self::OtherColoured => true,
            default => false,
        };
    }
}
