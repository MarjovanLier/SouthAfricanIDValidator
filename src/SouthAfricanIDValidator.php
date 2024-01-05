<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator;

use MarjovanLier\StringManipulation\StringManipulation;

/**
 * @psalm-suppress UnusedClass
 */
class SouthAfricanIDValidator
{
    /**
     * Validates a South African ID number based on its structural and contextual rules.
     *
     * The South African ID number is a 13-digit number which adheres to the following format: YYMMDDSSSSCAZ.
     *
     * Breakdown:
     * - YYMMDD (positions 1-6): Represents the date of birth. While it usually corresponds to the person's
     *   actual birthdate, there are rare cases where it might not.
     * - S (positions 7-10): Gender determination. Values 0-4 indicate females and 5-9 indicate males.
     * - C (position 11): Citizenship status.
     *   - 0: South African citizen
     *   - 1: Permanent resident
     *   - 2: Refugee
     * - A (position 12): Race indicator, used up to the late 1980s and now deprecated.
     *   - 0: White
     *   - 1: Cape Coloured
     *   - 2: Malay
     *   - 3: Griqua
     *   - 4: Chinese
     *   - 5: Indian
     *   - 6: Other Asian
     *   - 7: Other Coloured
     *   - 8: Generic category used post-1994
     *   - 9: Not sure, but found one reference for it
     * - Z (position 13): Checksum digit, validated using the Luhn algorithm.
     *
     * This function validates the ID number by ensuring:
     * - It has the correct length.
     * - The date component is valid.
     * - The eleventh character adheres to citizenship rules.
     * - The twelfth character (race) is valid.
     * - The entire ID number is valid as per the Luhn algorithm.
     *
     * @param string $number The South African ID number to be validated.
     *
     * @return null|bool True if the ID number is valid. False if it's invalid.
     *                   Null if certain positions in the ID don't meet specific constraints.
     *
     * @see https://en.wikipedia.org/wiki/South_African_identity_card
     */
    public static function luhnIDValidate(string $number): ?bool
    {
        $number = self::sanitizeNumber($number);

        if (strlen($number) !== 13) {
            return false;
        }

        if (!self::isValidEleventhCharacter($number)) {
            return null;
        }

        if (!self::isValidDateInID($number)) {
            return false;
        }

        return self::isValidLuhnChecksum($number);
    }


    /**
     * Validates the date component of a South African ID number.
     *
     * This function checks if the given date string, represented as YYMMDD, is a valid date
     * from one of the three specified centuries: 18th, 19th, or 20th.
     *
     * For instance:
     * - 880101 would be validated as January 1, 1888.
     * - 990101 would be validated as January 1, 1999.
     * - 000229 would be validated as February 29, 2000 (a leap year).
     *
     * Internally, this function uses the following methods:
     * - @param string $date The date component of the ID, in the format YYMMDD.
     *
     * @return bool True if the date is valid for any of the specified centuries, otherwise false.
     *
     * @see self::isValidDateFor1800sOr1900s
     * @see self::isValidDateFor2000s
     */
    public static function isValidIDDate(string $date): bool
    {
        if (strlen($date) !== 6) {
            return false;
        }

        if (self::isValidDateFor1800sOr1900s($date)) {
            return true;
        }

        return self::isValidDateFor2000s($date);
    }


    /**
     * Sanitizes the given number by removing all non-numeric characters.
     *
     * This function ensures that the input string only contains digits. Any other characters,
     * such as letters, punctuation, or whitespace, will be stripped out. If the provided input
     * does not contain any digits, an empty string is returned.
     *
     * @param string $number The input string to be sanitized.
     *
     * @return string A sanitized version of the input string, containing only digits.
     */
    private static function sanitizeNumber(string $number): string
    {
        return (preg_replace('#\D#', '', $number) ?? '');
    }


    /**
     * Indicates citizenship.
     *       0 - if you are an SA citizen,
     *       1 - if you are a permanent resident.
     *       2 - if you are a refugee.
     */
    private static function isValidEleventhCharacter(string $number): bool
    {
        if (strlen($number) < 11) {
            return false;
        }

        $eleventhCharacter = $number[10];

        return in_array($eleventhCharacter, ['0', '1', '2'], true);
    }


    /**
     * Validates a date in the YYMMDD format (e.g., 870110 for 10 Jan 1987).
     *
     * @param string $number A string representing a date in YYMMDDxxxxxx format.
     *
     * @return bool True if the date is valid; otherwise, false.
     */
    private static function isValidDateInID(string $number): bool
    {
        $ymd = substr($number, 0, 6);

        return static::isValidIDDate($ymd);
    }


    /**
     * Checks if a date string in 'Ymd' format belongs to the 18th or 19th century.
     *
     * This function expects the date string without the century prefix, i.e.,
     * it should only contain the year's last two digits, month, and day.
     * To validate, the function internally prefixes '18' to the provided date string
     * and checks its validity. Given the consistent day and month lengths across
     * the 1800s and 1900s, a valid date for the 1800s will also be valid for the
     * 1900s and vice versa. Hence, we only validate against the 1800s.
     *
     * @param string $date A 6-character date string in the format 'ymd',
     *                     representing a date in the 18th or 19th
     *                     century.
     *
     * @return bool Returns true if the date string represents a valid
     *              date in either the 18th or 19th century, false otherwise.
     *
     * @psalm-suppress UnusedMethod
     */
    private static function isValidDateFor1800sOr1900s(string $date): bool
    {
        if (strlen($date) !== 6) {
            return false;
        }

        // Validate for 1800s.
        // Given the consistent day and month lengths across the 1800s and 1900s,
        // a valid date for the 1800s will also be valid for the 1900s and vice versa.
        // Therefore, we only need to validate against one of these centuries.
        return StringManipulation::isValidDate('18' . $date, 'Ymd');
    }


    /**
     * Checks if a date belongs to the 20th century in 'Ymd' format.
     *
     * This function expects the date string without the century,
     * so only the year's last two digits, month, and day is provided.
     * The function internally prefixes '20' to the provided date string
     * and then checks its validity.
     *
     * @param string $date A 6-character date string in the format 'ymd',
     *                     representing a date in the 19th century.
     *
     * @return bool Returns true if the date string represents a valid
     *              date in the 19th century, false otherwise.
     *
     * @psalm-suppress UnusedMethod
     */
    private static function isValidDateFor2000s(string $date): bool
    {
        if (strlen($date) !== 6) {
            return false;
        }

        return StringManipulation::isValidDate('20' . $date, 'Ymd');
    }


    /**
     * Determines if the given number is valid according to the Luhn algorithm.
     *
     * The Luhn algorithm is a simple checksum formula used to validate a variety of identification numbers,
     * especially credit card numbers. The algorithm works by doubling every second digit from the right and
     * subtracting 9 from any resulting value greater than or equal to 10. The sum of all the digits (including
     * the doubled ones) is taken. The number is considered valid if sum modulo 10 is zero.
     *
     * @param string $number The number to be validated.
     *
     * @return bool True if the number is valid, according to the Luhn algorithm, false otherwise.
     *
     * @see https://en.wikipedia.org/wiki/Luhn_algorithm
     */
    private static function isValidLuhnChecksum(string $number): bool
    {
        // Check for non-numeric characters
        if (!ctype_digit($number)) {
            return false;
        }

        $total = 0;
        // Start with no doubling for the rightmost digit
        $double = false;

        // Iterate over the number from rightmost to leftmost
        for ($i = (strlen($number) - 1); $i >= 0; --$i) {
            /**
             * Explicit casting.
             *
             * @infection-ignore-all
             */
            $digit = (int)$number[$i];

            if ($double) {
                $digit *= 2;

                if ($digit >= 10) {
                    $digit -= 9;
                }
            }

            /**
             * @infection-ignore-all
             */
            $total += $digit;
            // Toggle the double flag for the next iteration
            $double = !$double;
        }

        return ($total % 10) === 0;
    }
}
