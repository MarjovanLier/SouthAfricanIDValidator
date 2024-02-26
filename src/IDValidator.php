<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator;

use InvalidArgumentException;

/**
 * Provides functionality to validate South African ID numbers.
 */
class IDValidator
{
    /**
     * Validates the provided South African ID number.
     *
     * @param string $identityNumber The ID number to be validated.
     *
     * @return bool Returns true if the ID number is valid, false otherwise.
     *
     * @throws InvalidArgumentException If the ID number is not in the correct format.
     */
    public function validate(string $identityNumber): bool
    {
        // Ensure the ID number matches the expected format
        if (!$this->matchesFormat($identityNumber)) {
            throw new InvalidArgumentException('The provided ID number does not match the expected format.');
        }

        // Validate the date of birth encoded in the ID number
        if (!$this->isValidDateOfBirth(substr($identityNumber, 0, 6))) {
            return false;
        }

        // Check if the sequence number indicates a male or female
        // Sequence numbers from 0000 to 4999 indicate a female, and from 5000 to 9999 indicate a male.
        $sequenceNumber = substr($identityNumber, 6, 4);
        $gender = (int) $sequenceNumber < 5000 ? 'female' : 'male';

        // Validate the last digit (check digit) using the Luhn algorithm
        return $this->isValidCheckDigit($identityNumber);
    }


    /**
     * Checks if the ID number matches the expected format.
     *
     * @param string $identityNumber The ID number to check.
     *
     * @return bool Returns true if the format is correct, false otherwise.
     */
    private function matchesFormat(string $identityNumber): bool
    {
        return preg_match('/^\d{13}$/', $identityNumber) === 1;
    }


    /**
     * Validates the date of birth encoded in the ID number.
     *
     * @param string $dob The date of birth substring from the ID number.
     *
     * @return bool Returns true if the date of birth is valid, false otherwise.
     */
    private function isValidDateOfBirth(string $dob): bool
    {
        // Extract the year, month, and day from the date of birth substring
        $year = (int) substr($dob, 0, 2);
        $month = (int) substr($dob, 2, 2);
        $day = (int) substr($dob, 4, 2);

        // Check if the extracted date is valid
        return checkdate($month, $day, $year);
    }


    /**
     * Validates the check digit of the ID number using the Luhn algorithm.
     *
     * @return bool Returns true if the check digit is valid, false otherwise.
     */
    private function isValidCheckDigit(): bool
    {
        // Implementation of the Luhn algorithm to validate the check digit
        // This is a placeholder for the actual implementation
        return true;
    }
}
