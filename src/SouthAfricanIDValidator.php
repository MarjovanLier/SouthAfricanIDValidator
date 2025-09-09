<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator;

use MarjovanLier\StringManipulation\StringManipulation;

/**
 * This class validates South African ID numbers.
 *
 * A South African ID is a 13-digit number adhering to the format: YYMMDDSSSSCAZ.
 * The 13-digit ID system was introduced with the green ID book in 1980,
 * replacing the 9-digit system used in the blue "Book of Life" (1972-1979).
 *
 * The format comprises the following components:
 * - YYMMDD (positions 1-6): Represents the date of birth.
 * - S (positions 7-10): Indicates gender.
 *     Values between 0-4 denote a female, and values between 5-9 denote a male.
 * - C (position 11): Signifies citizenship status.
 *     '0' for a South African citizen, '1' for a permanent resident, and '2' for a refugee.
 * - A (position 12): A race indicator, discontinued after the late 1980s but still must be 0-9.
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
final class SouthAfricanIDValidator
{
    /**
     * Holds the regular expression pattern for removing non-digit characters.
     *
     * This is utilised in the `sanitiseNumber` method to identify and remove non-digit characters
     * from the input string. The pattern `#\D#` matches any character that is not a digit.
     *
     * @see self::sanitiseNumber
     */
    private const string NON_DIGIT_REGEX = '#\D#';


    /**
     * Validates a South African ID number based on its structural and contextual criteria.
     *
     * The South African ID number is a 13-digit sequence following the format: YYMMDDSSSSCAZ.
     *
     * Breakdown:
     * - YYMMDD (positions 1-6): Date of birth
     * - SSSS (positions 7-10): Gender (0000-4999 for females, 5000-9999 for males)
     * - C (position 11): Citizenship status:
     *   - 0: South African citizen
     *   - 1: Permanent resident
     *   - 2: Refugee
     * - A (position 12): Race indicator (deprecated since late 1980s):
     *   - 0: White
     *   - 1: Cape Coloured
     *   - 2: Malay
     *   - 3: Griqua
     *   - 4: Chinese
     *   - 5: Indian
     *   - 6: Other Asian
     *   - 7: Other Coloured
     *   - 8: Generic category used post-1994
     *   - 9: Not clearly documented, but found in some references
     * - Z (position 13): Checksum digit (Luhn algorithm)
     *
     * Historical context:
     * The 13-digit ID system was introduced with the green ID book in 1980, and was
     * issued to all citizens including adults. The system replaced the 9-digit format
     * used in the blue "Book of Life" (1972-1979). A 90-year-old in 1986 would have
     * an ID starting with "96" representing birth year 1896.
     *
     * Validation process:
     * 1. Removes non-numeric characters from input
     * 2. Checks length is exactly 13 digits
     * 3. Validates citizenship digit (0, 1, or 2)
     * 4. Validates date of birth:
     *    - Must be a valid calendar date
     *    - Tests three possible centuries (1800s, 1900s, 2000s)
     *    - Cannot determine actual age or future dates due to 2-digit year limitation
     * 5. Validates race indicator (must be 0-9)
     * 6. Validates Luhn checksum
     *
     * Return values:
     * - true: The ID number is completely valid
     * - false: Structural issues (wrong length, invalid date, invalid race indicator,
     *          or failed checksum)
     * - null: Invalid citizenship digit (not 0, 1, or 2). This special case is
     *         maintained for backward compatibility.
     *
     * Note: The null return for invalid citizenship is a legacy behaviour. Consider
     * it as a specific type of validation failure rather than an absence of result.
     *
     * @param string $number The South African ID number to validate (may contain formatting).
     *
     * @return bool|null True if valid, false if structurally invalid, null if citizenship invalid.
     * @see    https://en.wikipedia.org/wiki/South_African_identity_card
     */
    public static function luhnIDValidate(string $number): ?bool
    {
        // Remove all non-numeric characters from the input
        $number = self::sanitiseNumber($number);

        // If the sanitised number is not exactly 13 characters long, return false
        if (\strlen($number) !== 13) {
            return false;
        }

        // If the eleventh character does not comply with citizenship rules, return null
        if (!self::isValidCitizenshipDigit($number)) {
            return null;
        }

        // If the date part is not valid, return false
        if (!self::isValidDateInID($number)) {
            return false;
        }

        // Check the entire number against the Luhn algorithm and return the result
        // Note: Race indicator validation is not needed since we already know it is a digit
        return self::isValidLuhnChecksum($number);
    }


    /**
     * Validates the date part of a South African ID number.
     *
     * This checks if the YYMMDD date string is a valid date. The 13-digit ID
     * system was introduced in 1980 with the green ID book, and adults of all
     * ages received IDs, including those born in the 1800s.
     *
     * For a date to be valid:
     * - Must be exactly 6 characters long
     * - Must contain only digits
     * - Must represent a valid calendar date
     *
     * Century determination:
     * - Tests three possible centuries: 1800s, 1900s, and 2000s
     * - Since we only have YY (not YYYY), we cannot determine the actual century
     * - We accept any YY that forms a valid date in at least one century
     * - The 13-digit ID system was introduced in 1980 and included people born in the 1800s
     *
     * @param string $date The date part of the ID, in YYMMDD format.
     *
     * @return bool True if the date is valid and within acceptable range, false otherwise.
     */
    public static function isValidIDDate(string $date): bool
    {
        // If the date string is not 6 characters long, return false
        if (\strlen($date) !== 6) {
            return false;
        }

        // Date string must contain only digits
        if (!ctype_digit($date)) {
            return false;
        }

        // Test three possible centuries
        // The ID system includes people born in the 1800s, 1900s, and 2000s
        $centuries = ['18', '19', '20'];
        foreach ($centuries as $century) {
            if (StringManipulation::isValidDate($century . $date, 'Ymd')) {
                return true;
            }
        }

        return false;
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
    private static function sanitiseNumber(string $number): string
    {
        // If the input is already all digits, return it as is
        if (\ctype_digit($number)) {
            return $number;
        }

        // Replace all non-digit characters with an empty string
        return \preg_replace(self::NON_DIGIT_REGEX, '', $number) ?? '';
    }


    /**
     * Validates the citizenship digit (11th character) of a South African ID number.
     *
     * The eleventh character signifies citizenship status, with valid values being '0', '1', or '2'.
     * The possible values are:
     *  - '0': South African citizen
     *  - '1': Permanent resident
     *  - '2': Refugee
     *
     * This verifies whether the citizenship digit is one of these valid values.
     * Note: This method assumes the input has already been validated to be 13 digits.
     *
     * @param string $number The South African ID number to check (must be 13 digits).
     *
     * @return bool True if the citizenship digit is valid, false otherwise.
     */
    private static function isValidCitizenshipDigit(string $number): bool
    {
        // Extract the eleventh character and check if it is a valid citizenship status
        $eleventhCharacter = $number[10];

        // Return the result of the check
        return \in_array($eleventhCharacter, ['0', '1', '2'], true);
    }


    /**
     * Validates the date component within a South African ID number.
     *
     * Extracts the first six characters (YYMMDD) and validates it as a date.
     *
     * @param string $number The South African ID number to check.
     *
     * @return bool True if the date component is valid, false otherwise.
     * @see    self::isValidIDDate
     */
    private static function isValidDateInID(string $number): bool
    {
        // Extract the date part and validate it
        return self::isValidIDDate(\substr($number, 0, 6));
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
     * @see    https://en.wikipedia.org/wiki/Luhn_algorithm
     */
    private static function isValidLuhnChecksum(string $number): bool
    {
        // Check for non-numeric characters
        if (!\ctype_digit($number)) {
            return false;
        }

        $total = 0;
        // Start with no doubling for the rightmost digit
        $double = false;

        // Iterate over the number from rightmost to leftmost
        for ($i = (\strlen($number) - 1); $i >= 0; --$i) {
            $digit = (int) $number[$i];

            if ($double) {
                $digit *= 2;

                if ($digit >= 10) {
                    $digit -= 9;
                }
            }

            $total += $digit;
            // Toggle the double flag for the next iteration
            $double = !$double;
        }

        return ($total % 10) === 0;
    }


    /**
     * Converts a legacy South African ID to modern format.
     *
     * Changes the race indicator (position 12) from legacy values (0-7) to a
     * modern indicator (8 or 9) and recalculates the checksum.
     *
     * Historical context:
     * The Identification Act of 1986 initiated the removal of racial criteria
     * from identity numbers, signalling a move away from the apartheid system.
     * Following this legislative change, old ID numbers were reissued, and all
     * new numbers were generated without racial identifiers.
     *
     * Legacy race indicators (0-7) were used during the apartheid era:
     * - 0: White
     * - 1: Cape Coloured
     * - 2: Malay
     * - 3: Griqua
     * - 4: Chinese
     * - 5: Indian
     * - 6: Other Asian
     * - 7: Other Coloured
     *
     * Modern indicators (8-9) are used post-apartheid:
     * - 8: Generic category (standard modern format, default)
     * - 9: Alternative indicator (used administratively to prevent duplicates)
     *
     * @param string $legacyId         The legacy SA ID number to convert.
     * @param int    $modernIndicator  The modern race indicator to use (8 or 9, defaults to 8).
     *
     * @return string|null The modernised ID with the specified race indicator, or null if input is invalid.
     */
    public static function convertLegacyToModern(string $legacyId, int $modernIndicator = 8): ?string
    {
        // Validate modern indicator parameter
        if (!\in_array($modernIndicator, [8, 9], true)) {
            return null;
        }

        // Validate the input ID
        $validationResult = self::luhnIDValidate($legacyId);
        if ($validationResult !== true) {
            return null;
        }

        // Check if it is a legacy ID (race indicator 0-7)
        $raceDigit = $legacyId[11];
        if (!\in_array($raceDigit, ['0', '1', '2', '3', '4', '5', '6', '7'], true)) {
            // Already modern format (8 or 9)
            return $legacyId;
        }

        // Replace race indicator with the specified modern indicator
        $modernId = \substr($legacyId, 0, 11) . (string) $modernIndicator;

        // Recalculate checksum using optimised Luhn algorithm
        $sum = 0;
        $double = true; // Start with doubling for the 12th digit (position 11) from right

        // Process digits in reverse, doubling every second digit
        for ($i = 11; $i >= 0; --$i) {
            $digit = (int) $modernId[$i];

            if ($double) {
                $digit <<= 1; // Bit shift is faster than multiplication
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = !$double; // Toggle doubling for next digit
        }

        $checksum = (10 - ($sum % 10)) % 10;

        return $modernId . (string) $checksum;
    }


    /**
     * Extracts comprehensive information from a South African ID number.
     *
     * Returns an array containing all extractable components from the ID.
     * Note: The century cannot be definitively determined from the ID alone
     * as it only contains a 2-digit year.
     *
     * @param string $idNumber The South African ID number to analyse.
     *
     * @return (bool|null|string|string[])[]
     *
     * @psalm-return array{valid: bool, date_components: array{year: string, month: string, day: string}|null, gender: null|string, citizenship: null|string, is_legacy: bool, race_indicator: null|string}
     */
    public static function extractInfo(string $idNumber): array
    {
        $sanitised = self::sanitiseNumber($idNumber);
        $isValid = self::luhnIDValidate($sanitised) !== false && self::luhnIDValidate($sanitised) !== null;

        if (!$isValid || \strlen($sanitised) !== 13) {
            return [
                'valid' => false,
                'date_components' => null,
                'gender' => null,
                'citizenship' => null,
                'is_legacy' => false,
                'race_indicator' => null,
            ];
        }

        return [
            'valid' => true,
            'date_components' => self::extractDateComponents($sanitised),
            'gender' => self::extractGender($sanitised),
            'citizenship' => self::extractCitizenship($sanitised),
            'is_legacy' => self::isLegacyID($sanitised),
            'race_indicator' => $sanitised[11],
        ];
    }


    /**
     * Extracts date components from a South African ID number.
     *
     * Returns the year (2-digit), month, and day from the ID.
     * Note: Century cannot be determined from the 2-digit year alone.
     *
     * @param string $idNumber The South African ID number (must be 13 digits).
     *
     * @return array{year: string, month: string, day: string}|null Date components or null if invalid.
     */
    public static function extractDateComponents(string $idNumber): ?array
    {
        $sanitised = self::sanitiseNumber($idNumber);

        if (\strlen($sanitised) !== 13) {
            return null;
        }

        $year = \substr($sanitised, 0, 2);
        $month = \substr($sanitised, 2, 2);
        $day = \substr($sanitised, 4, 2);

        // Validate the date components
        if (!self::isValidIDDate($year . $month . $day)) {
            return null;
        }

        return [
            'year' => $year,
            'month' => $month,
            'day' => $day,
        ];
    }


    /**
     * Extracts gender from a South African ID number.
     *
     * The sequence number (positions 7-10) indicates gender:
     * - 0000-4999: Female
     * - 5000-9999: Male
     *
     * @param string $idNumber The South African ID number (must be 13 digits).
     *
     * @return null|string 'female', 'male', or null if invalid.
     *
     * @psalm-return 'female'|'male'|null
     */
    public static function extractGender(string $idNumber): string|null
    {
        $sanitised = self::sanitiseNumber($idNumber);

        if (\strlen($sanitised) !== 13) {
            return null;
        }

        $sequenceNumber = (int) \substr($sanitised, 6, 4);

        return $sequenceNumber < 5000 ? 'female' : 'male';
    }


    /**
     * Extracts citizenship status from a South African ID number.
     *
     * The 11th digit indicates citizenship:
     * - 0: South African citizen
     * - 1: Permanent resident
     * - 2: Refugee
     *
     * @param string $idNumber The South African ID number (must be 13 digits).
     *
     * @return null|string Citizenship status or null if invalid.
     *
     * @psalm-return 'permanent_resident'|'refugee'|'south_african_citizen'|null
     */
    public static function extractCitizenship(string $idNumber): string|null
    {
        $sanitised = self::sanitiseNumber($idNumber);

        if (\strlen($sanitised) !== 13) {
            return null;
        }

        $citizenshipDigit = $sanitised[10];

        return match ($citizenshipDigit) {
            '0' => 'south_african_citizen',
            '1' => 'permanent_resident',
            '2' => 'refugee',
            default => null,
        };
    }


    /**
     * Checks if a South African ID number uses the legacy format.
     *
     * Legacy IDs have race indicators 0-7 in position 12.
     * Modern IDs use 8 or 9 in this position.
     *
     * @param string $idNumber The South African ID number to check.
     *
     * @return bool True if legacy format (race indicators 0-7), false otherwise.
     */
    public static function isLegacyID(string $idNumber): bool
    {
        $sanitised = self::sanitiseNumber($idNumber);

        if (\strlen($sanitised) !== 13) {
            return false;
        }

        $raceIndicator = $sanitised[11];

        return \in_array($raceIndicator, ['0', '1', '2', '3', '4', '5', '6', '7'], true);
    }


    /**
     * Batch validates multiple South African ID numbers.
     *
     * Processes an array of ID numbers and returns their validation status.
     *
     * @param array<int|string, mixed> $idNumbers Array of ID numbers to validate.
     *
     * @return array{}|non-empty-array<string,?bool> Array keyed by ID number with validation results.
     */
    public static function batchValidate(array $idNumbers): array
    {
        $results = [];

        foreach ($idNumbers as $idNumber) {
            if (!\is_string($idNumber)) {
                continue;
            }

            $results[$idNumber] = self::luhnIDValidate($idNumber);
        }

        return $results;
    }


    /**
     * Checks if two South African IDs would be duplicates.
     *
     * Two IDs are potential duplicates if they share the same first 11 digits
     * (date, sequence, citizenship). Such cases require using digit 9 instead
     * of 8 in position 12 to differentiate them.
     *
     * @param string $id1 First South African ID number.
     * @param string $id2 Second South African ID number.
     *
     * @return bool True if they share the same first 11 digits, false otherwise.
     */
    public static function wouldBeDuplicates(string $id1, string $id2): bool
    {
        $sanitised1 = self::sanitiseNumber($id1);
        $sanitised2 = self::sanitiseNumber($id2);

        // Both must be valid 13-digit IDs
        if (\strlen($sanitised1) !== 13 || \strlen($sanitised2) !== 13) {
            return false;
        }

        // Compare first 11 digits
        return \substr($sanitised1, 0, 11) === \substr($sanitised2, 0, 11);
    }
}
