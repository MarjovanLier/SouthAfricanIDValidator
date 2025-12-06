<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Enum;

/**
 * Represents citizenship status as encoded in South African ID numbers.
 *
 * The 11th digit indicates citizenship:
 * - 0: South African citizen
 * - 1: Permanent resident
 * - 2: Refugee
 */
enum Citizenship: string
{
    case SouthAfricanCitizen = 'south_african_citizen';
    case PermanentResident = 'permanent_resident';
    case Refugee = 'refugee';


    /**
     * Creates a Citizenship enum from the citizenship digit.
     *
     * @param string $digit The citizenship digit (0, 1, or 2).
     *
     * @return self|null Null if the digit is invalid.
     */
    public static function fromDigit(string $digit): ?self
    {
        return match ($digit) {
            '0' => self::SouthAfricanCitizen,
            '1' => self::PermanentResident,
            '2' => self::Refugee,
            default => null,
        };
    }
}
