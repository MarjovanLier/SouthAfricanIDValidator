<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Enum;

/**
 * Represents gender as encoded in South African ID numbers.
 *
 * The sequence number (positions 7-10) indicates gender:
 * - 0000-4999: Female
 * - 5000-9999: Male
 */
enum Gender: string
{
    case Female = 'female';
    case Male = 'male';


    /**
     * Creates a Gender enum from a sequence number.
     *
     * @param int $sequenceNumber The 4-digit sequence number (0-9999).
     */
    public static function fromSequenceNumber(int $sequenceNumber): self
    {
        if ($sequenceNumber < 5000) {
            return self::Female;
        }

        return self::Male;
    }
}
