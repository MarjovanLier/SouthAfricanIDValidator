<?php

namespace SouthAfricanIDValidator;

use InvalidArgumentException;

/**
 * Class IDValidator
 * Provides functionality to validate South African ID numbers.
 */
class IDValidator
{
    /**
     * Validates the provided South African ID number.
     *
     * @param string $id The ID number to be validated.
     * @return bool Returns true if the ID number is valid, false otherwise.
     * @throws InvalidArgumentException If the ID number is not in the correct format.
     */
    public function validate(string $id): bool
    {
        // Ensure the ID number matches the expected format
        if (!$this->matchesFormat($id)) {
            throw new InvalidArgumentException('The provided ID number does not match the expected format.');
        }

        // Validate the date of birth encoded in the ID number
        if (!$this->isValidDateOfBirth(substr($id, 0, 6))) {
            return false;
        }

        // Check if the sequence number indicates a male or female
        // Sequence numbers from 0000 to 4999 indicate a female, and from 5000 to 9999 indicate a male.
        $sequenceNumber = substr($id, 6, 4);
        $gender = (int)$sequenceNumber < 5000 ? 'female' : 'male';

        // Validate the last digit (check digit) using the Luhn algorithm
        return $this->isValidCheckDigit($id);
    }

    /**
     * Checks if the ID number matches the expected format.
     *
     * @param string $id The ID number to check.
     * @return bool Returns true if the format is correct, false otherwise.
     */
    private function matchesFormat(string $id): bool
    {
        return preg_match('/^\d{13}$/', $id) === 1;
    }

    /**
     * Validates the date of birth encoded in the ID number.
     *
     * @param string $dob The date of birth substring from the ID number.
     * @return bool Returns true if the date of birth is valid, false otherwise.
     */
    private function isValidDateOfBirth(string $dob): bool
    {
        // Extract the year, month, and day from the date of birth substring
        $year = intval(substr($dob, 0, 2));
        $month = intval(substr($dob, 2, 2));
        $day = intval(substr($dob, 4, 2));

        // Check if the extracted date is valid
        return checkdate($month, $day, $year);
    }

    /**
     * Validates the check digit of the ID number using the Luhn algorithm.
     *
     * @param string $id The ID number to validate.
     * @return bool Returns true if the check digit is valid, false otherwise.
     */
    private function isValidCheckDigit(string $id): bool
    {
        // Implementation of the Luhn algorithm to validate the check digit
        // This is a placeholder for the actual implementation
        return true;
    }
}
