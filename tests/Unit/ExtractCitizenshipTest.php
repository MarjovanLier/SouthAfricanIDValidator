<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the extractCitizenship method in SouthAfricanIDValidator.
 */
final class ExtractCitizenshipTest extends TestCase
{
    /**
     * Tests extractCitizenship with South African citizen (0).
     */
    public function testExtractCitizenshipWithSouthAfricanCitizen(): void
    {
        $idNumber = '8001015009087'; // Citizenship digit is 0

        $result = SouthAfricanIDValidator::extractCitizenship($idNumber);

        $this->assertSame('south_african_citizen', $result, 'Should identify SA citizen');
    }

    /**
     * Tests extractCitizenship with permanent resident (1).
     */
    public function testExtractCitizenshipWithPermanentResident(): void
    {
        $idNumber = '8001015009186'; // Valid 13-digit ID with citizenship digit 1

        $result = SouthAfricanIDValidator::extractCitizenship($idNumber);

        $this->assertSame('permanent_resident', $result, 'Should identify permanent resident');
    }

    /**
     * Tests extractCitizenship with refugee (2).
     */
    public function testExtractCitizenshipWithRefugee(): void
    {
        $idNumber = '8001015009285'; // Valid 13-digit ID with citizenship digit 2

        $result = SouthAfricanIDValidator::extractCitizenship($idNumber);

        $this->assertSame('refugee', $result, 'Should identify refugee');
    }

    /**
     * Tests extractCitizenship with invalid citizenship digit.
     */
    public function testExtractCitizenshipWithInvalidCitizenshipDigit(): void
    {
        // Generate ID with invalid citizenship digit (3)
        $idNumber = '8001015009387';

        $result = SouthAfricanIDValidator::extractCitizenship($idNumber);

        $this->assertNull($result, 'Should return null for invalid citizenship digit');
    }

    /**
     * Tests extractCitizenship with invalid ID length.
     */
    public function testExtractCitizenshipWithInvalidLength(): void
    {
        $idNumber = '123456789'; // Too short

        $result = SouthAfricanIDValidator::extractCitizenship($idNumber);

        $this->assertNull($result, 'Should return null for invalid length');
    }

    /**
     * Tests extractCitizenship with non-numeric characters.
     */
    public function testExtractCitizenshipWithNonNumericCharacters(): void
    {
        $idNumber = '80-01-01 5009-087'; // Valid citizen ID with formatting

        $result = SouthAfricanIDValidator::extractCitizenship($idNumber);

        $this->assertSame('south_african_citizen', $result, 'Should extract citizenship after sanitisation');
    }

    /**
     * Tests extractCitizenship with all valid citizenship values.
     */
    public function testExtractCitizenshipWithAllValidValues(): void
    {
        // Use properly generated valid 13-digit IDs with correct citizenship values
        $testCases = [
            ['id' => '8001015009087', 'expected' => 'south_african_citizen', 'digit' => '0'],
            ['id' => '8001015009186', 'expected' => 'permanent_resident', 'digit' => '1'],
            ['id' => '8001015009285', 'expected' => 'refugee', 'digit' => '2'],
        ];

        foreach ($testCases as $testCase) {
            $result = SouthAfricanIDValidator::extractCitizenship($testCase['id']);
            $this->assertSame(
                $testCase['expected'],
                $result,
                sprintf('Citizenship digit %s should map to %s', $testCase['digit'], $testCase['expected']),
            );
        }
    }
}
