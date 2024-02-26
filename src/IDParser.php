<?php

namespace SouthAfricanIDValidator;

/**
 * Class IDParser
 * Parses South African ID numbers and extracts information such as date of birth, gender, and citizenship status.
 */
class IDParser
{
    /**
     * Parses the given South African ID number and extracts information.
     *
     * @param string $id The South African ID number to parse.
     * @return array An associative array containing the date of birth, gender, and citizenship status.
     */
    public function parse(string $id): array
    {
        $dob = $this->extractDateOfBirth($id);
        $gender = $this->determineGender($id);
        $citizenship = $this->determineCitizenship($id);

        return [
            'date_of_birth' => $dob,
            'gender' => $gender,
            'citizenship' => $citizenship,
        ];
    }

    /**
     * Extracts the date of birth from the ID number.
     *
     * @param string $id The ID number.
     * @return string The date of birth in YYYY-MM-DD format.
     */
    private function extractDateOfBirth(string $id): string
    {
        $year = substr($id, 0, 2);
        $month = substr($id, 2, 2);
        $day = substr($id, 4, 2);

        // Determine the century of the birth year
        $currentYearLastTwoDigits = date('y');
        $century = $year <= $currentYearLastTwoDigits ? '20' : '19';

        return $century . $year . '-' . $month . '-' . $day;
    }

    /**
     * Determines the gender based on the ID number.
     *
     * @param string $id The ID number.
     * @return string The gender ('male' or 'female').
     */
    private function determineGender(string $id): string
    {
        $genderCode = substr($id, 6, 4);
        return (int)$genderCode < 5000 ? 'female' : 'male';
    }

    /**
     * Determines the citizenship status based on the ID number.
     *
     * @param string $id The ID number.
     * @return string The citizenship status ('SA citizen' or 'permanent resident').
     */
    private function determineCitizenship(string $id): string
    {
        $citizenshipCode = substr($id, 10, 1);
        return $citizenshipCode === '0' ? 'SA citizen' : 'permanent resident';
    }
}
