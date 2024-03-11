<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator;

use MarjovanLier\StringManipulation\StringManipulation;

/**
 * This class is tasked with validating South African ID numbers.
 *
 * A South African ID is a 13-digit number adhering to the format: YYMMDDSSSSCAZ.
 *
 * Here's an explanation of the format:
 * - YYMMDD (positions 1-6): Represents the date of birth.
 * - S (positions 7-10): Indicates gender.
 *     Values between 0-4 denote a female, and values between 5-9 denote a male.
 * - C (position 11): Signifies citizenship status.
 *     '0' for a South African citizen, '1' for a permanent resident, and '2' for a refugee.
 * - A (position 12): A race indicator, its use was discontinued after the late 1980s.
 * - Z (position 13): A checksum digit, verified using the Luhn algorithm.
 *
 * The ID number validation rules include:
 * - Correct length of the ID number.
 * - Validity of the date part of the ID number.
 * - Compliance of the 11th character with citizenship status rules.
 * - Validity of the 12th character (race).
 * - Passing the Luhn algorithm check.
 *
 * For further information, visit: https://en.wikipedia.org/wiki/South_African_identity_card
 */
class SouthAfricanIDValidator
{
    /**
     * Holds the regular expression pattern for removing non-digit characters.
     *
     * This is used in the `sanitizeNumber` method to find and eliminate any non-digit characters
     * from the input string. The pattern `#\D#` finds any character that is not a digit.
     *
     * @see self::sanitizeNumber
     */
    private const NON_DIGIT_REGEX = '#\D#';


    /**
     * Validates a South African ID number based on its structural and contextual criteria.
     *
     * The South African ID number is a 13-digit sequence following the format: YYMMDDSSSSCAZ.
     *
     * Breakdown:
     * - YYMMDD (positions 1-6): Signifies the date of birth, which typically matches the individual's
     *   actual birth date, though exceptions exist.
     * - S (positions 7-10): Determines gender, with 0-4 for females and 5-9 for males.
     * - C (position 11): Citizenship status, where:
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
     * This method validates the ID number by checking:
     * - Its length.
     * - The validity of the date part.
     * - Compliance of the eleventh character with citizenship rules.
     * - Validity of the twelfth character (race).
     * - Overall validity according to the Luhn algorithm.
     *
     * Initially, it sanitizes the input by removing all non-numeric characters using the
     * `self::sanitizeNumber` method. Then, it checks if the sanitized string is exactly 13 characters long.
     * If not, it returns false. It then verifies if the eleventh character complies with citizenship rules,
     * returning null if it does not. Afterwards, it checks the validity of the date part. If invalid, it returns false.
     * Lastly, it checks the entire number against the Luhn algorithm and returns the result.
     *
     * @param string $number The South African ID number to validate.
     *
     * @return null|bool True if the ID number is valid, false if it's not, and null if specific criteria aren't met.
     *
     * @see https://en.wikipedia.org/wiki/South_African_identity_card
     */
    public static function luhnIDValidate(string $number): ?bool
    {
        // Remove all non-numeric characters from the input
        $number = self::sanitizeNumber($number);

        // If the sanitized number isn't exactly 13 characters long, return false
        if (strlen($number) !== 13) {
            return false;
        }

        // If the eleventh character doesn't comply with citizenship rules, return null
        if (!self::isValidEleventhCharacter($number)) {
            return null;
        }

        // If the date part isn't valid, return false
        if (!self::isValidDateInID($number)) {
            return false;
        }

        // Check the entire number against the Luhn algorithm and return the result
        return self::isValidLuhnChecksum($number);
    }


    /**
     * Validates the date part of a South African ID number.
     *
     * This checks if the YYMMDD date string is a valid date from the 18th, 19th, or 20th century.
     *
     * First, it ensures the date string is 6 characters long. If not, it returns false.
     * It then checks for validity in the 18th or 19th century dates. If valid, it returns true.
     * If not a valid 18th or 19th century date, it checks for 20th century validity and returns that result.
     *
     * Examples:
     * - 880101 is January 1, 1888.
     * - 990101 is January 1, 1999.
     * - 000229 is February 29, 2000 (a leap year).
     *
     * @param string $date The date part of the ID, in YYMMDD format.
     *
     * @return bool True if the date is valid for any specified century, false otherwise.
     *
     * @see self::isValidDateFor1800sOr1900s
     * @see self::isValidDateFor2000s
     */
    public static function isValidIDDate(string $date): bool
    {
        // If the date string isn't 6 characters long, return false
        if (strlen($date) !== 6) {
            return false;
        }

        // If the date string is a valid 18th or 19th century date, return true
        if (self::isValidDateFor1800sOr1900s($date)) {
            return true;
        }

        // Check if the date string is a valid 20th century date and return the result
        return self::isValidDateFor2000s($date);
    }


    /**
     * Removes all non-numeric characters from a given number.
     *
     * This ensures the input string contains only digits, stripping out any letters, punctuation, or whitespace.
     * If there are no digits in the input, an empty string is returned.
     *
     * It uses a regular expression to find non-digit characters and replaces them with an empty string.
     * If `preg_replace` fails, null is coalesced to an empty string.
     *
     * @param string $number The input string to clean.
     *
     * @return string A cleaned version of the input, containing only digits.
     */
    private static function sanitizeNumber(string $number): string
    {
        // If the input is already all digits, return it as is
        if (ctype_digit($number)) {
            return $number;
        }

        // Replace all non-digit characters with an empty string, coalesce null to an empty string if needed
        return (preg_replace(self::NON_DIGIT_REGEX, '', $number) ?? '');
    }


    /**
     * Validates the eleventh character of a South African ID number for citizenship status.
     *
     * The eleventh character signifies citizenship status, with valid values being '0', '1', or '2'.
     * The possible values are:
     *  - '0': South African citizen
     *  - '1': Permanent resident
     *  - '2': Refugee
     *
     * This checks if the eleventh character is one of these valid values.
     *
     * @param string $number The South African ID number to check.
     *
     * @return bool True if the eleventh character is a valid citizenship status, false otherwise.
     */
    private static function isValidEleventhCharacter(string $number): bool
    {
        // Return false if the ID number is shorter than 11 characters
        if (strlen($number) < 11) {
            return false;
        }

        // Extract the eleventh character and check if it's a valid citizenship status
        $eleventhCharacter = $number[10];

        // Return the result of the check
        return in_array($eleventhCharacter, ['0', '1', '2'], true);
    }


    /**
     * Validates the date component within a South African ID number.
     *
     * Extracts the first six characters (YYMMDD) and validates it as a date.
     *
     * Example:
     * - For an ID number 8701101234567, it validates 870110 as a date.
     *
     * @param string $number The South African ID number to check.
     *
     * @return bool True if the date component is valid, false otherwise.
     *
     * @see self::isValidIDDate
     */
    private static function isValidDateInID(string $number): bool
    {
        // Extract the date part and validate it
        $ymd = substr($number, 0, 6);

        // Return the result of the date validation
        return self::isValidIDDate($ymd);
    }


    /**
     * Checks if a date string pertains to the 18th or 19th century.
     *
     * It validates the date assuming it's from the 1800s, but due to consistent day/month lengths, a valid 1800s date
     * is also valid for the 1900s.
     * It returns true for a valid date in either century.
     *
     * Example:
     * - 010101 is January 1, 1801.
     * - 000229 is February 29, 1800 (a leap year).
     *
     * @param string $date A 6-character date string for an 18th or 19th-century date.
     *
     * @return bool True if it's a valid date in either century, false otherwise.
     *
     * @psalm-suppress UnusedMethod
     */
    private static function isValidDateFor1800sOr1900s(string $date): bool
    {
        // Return false if the date string isn't exactly 6 characters long
        if (strlen($date) !== 6) {
            return false;
        }

        // Validate the date prefixed with '18' for the 1800s
        return StringManipulation::isValidDate('18' . $date, 'Ymd');
    }


    /**
     * Checks if a date string pertains to the 20th century.
     *
     * Validates a date with the '20' prefix to check for 20th-century validity.
     *
     * Example:
     * - 010101 is January 1, 2001.
     * - 000229 is February 29, 2000 (a leap year).
     *
     * @param string $date A 6-character date string for a 20th-century date.
     *
     * @return bool True if it's a valid 20th-century date, false otherwise.
     *
     * @psalm-suppress UnusedMethod
     */
    private static function isValidDateFor2000s(string $date): bool
    {
        // Return false if the date string isn't exactly 6 characters long
        if (strlen($date) !== 6) {
            return false;
        }

        // Validate the date prefixed with '20' for the 2000s
        return StringManipulation::isValidDate('20' . $date, 'Ymd');
    }


    /**
     * Validates a number according to the Luhn algorithm.
     *
     * This method applies the Luhn algorithm to check for the validity of identification numbers, especially credit
     * cards.
     * The algorithm doubles every second digit from the right, subtracts 9 from numbers that are greater than or equal
     * to 10, and checks if the total modulo 10 is zero.
     *
     * @param string $number The number to validate.
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
            $digit = (int) $number[$i];

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
