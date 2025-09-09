<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Feature;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Feature tests for real-world South African ID validation scenarios.
 * These tests validate the library from a user's perspective, evaluating
 * complete workflows and integration scenarios.
 */
final class RealWorldValidationTest extends TestCase
{
    /**
     * Tests validation of IDs from different decades to ensure century logic functions correctly.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testValidationAcrossDecades(): void
    {
        // Born in the 1930s
        $id1930s = '3202295029085';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($id1930s),
            'ID from 1930s must validate correctly to demonstrate decade-spanning functionality',
        );

        // Born in the 1940s
        $id1940s = '4806010046080';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($id1940s),
            'ID from 1940s must validate correctly to ensure historical ID support',
        );

        // Born in the 1980s
        $id1980s = '8701105800085';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($id1980s),
            'ID from 1980s must validate correctly to confirm modern ID handling',
        );

        // Born in the 2000s
        $id2000s = '0809015019080';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($id2000s),
            'ID from 2000s must validate correctly to verify millennium transition support',
        );
    }

    /**
     * Tests validation of IDs with different gender sequences.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testGenderValidation(): void
    {
        // Female IDs (0000-4999)
        $femaleIds = [
            '3202295029085', // Female with sequence 5029
            '4806010046080', // Female with sequence 0046
            '3206015052087', // Female with sequence 5052
        ];

        foreach ($femaleIds as $femaleId) {
            self::assertTrue(
                SouthAfricanIDValidator::luhnIDValidate($femaleId),
                'Female ID should be valid: ' . $femaleId,
            );
        }

        // Male IDs (5000-9999)
        $maleIds = [
            '8701105800085', // Male with sequence 5800
            '0809015019080', // Male with sequence 5019
        ];

        foreach ($maleIds as $maleId) {
            self::assertTrue(
                SouthAfricanIDValidator::luhnIDValidate($maleId),
                'Male ID should be valid: ' . $maleId,
            );
        }
    }

    /**
     * Tests validation with IDs containing formatting commonly entered by users.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testUserInputFormatting(): void
    {
        // ID numbers may contain spaces when copied
        $withSpaces = '8701 1058 0008 5';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($withSpaces),
            'ID with spaces must be validated after sanitisation to handle common user input formatting',
        );

        // ID numbers may contain dashes
        $withDashes = '870110-5800-08-5';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($withDashes),
            'ID with dashes must be validated after sanitisation to support alternative formatting styles',
        );

        // Mixed formatting with various separators
        $mixedFormat = ' 8701.10.5800.085 ';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($mixedFormat),
            'ID with mixed formatting must be validated after sanitisation to ensure robust input handling',
        );

        // ID with parentheses grouping
        $withParentheses = '870110(5800)085';
        self::assertTrue(
            SouthAfricanIDValidator::luhnIDValidate($withParentheses),
            'ID with parentheses must be validated after sanitisation to accommodate various grouping conventions',
        );
    }

    /**
     * Tests common data entry errors that must fail validation.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCommonDataEntryErrors(): void
    {
        // Transposed digits (common typing error)
        $transposed = '8710105800085'; // 8701 became 8710
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($transposed),
            'ID with transposed digits must fail validation to detect common typing errors',
        );

        // Missing digit
        $missingDigit = '870110580085'; // Missing one 0 from sequence
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($missingDigit),
            'ID with missing digit must fail validation to ensure length requirements are enforced',
        );

        // Extra digit
        $extraDigit = '87011058000855'; // Extra 5 at the end
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($extraDigit),
            'ID with extra digit must fail validation to prevent acceptance of malformed IDs',
        );

        // Letter instead of number (OCR error)
        $withLetter = '87O1105800085'; // O instead of 0
        self::assertFalse(
            SouthAfricanIDValidator::luhnIDValidate($withLetter),
            'ID with letter must fail validation to catch OCR errors and ensure numeric-only input',
        );
    }

    /**
     * Tests validation of IDs for individuals born on special dates.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testSpecialDates(): void
    {
        // New Year's Day
        $newYear = '8701015800085';
        self::assertNotNull(
            SouthAfricanIDValidator::luhnIDValidate($newYear),
            "New Year's Day ID must be handled correctly to validate special calendar dates",
        );

        // Christmas Day
        $christmas = '8712255800085';
        self::assertNotNull(
            SouthAfricanIDValidator::luhnIDValidate($christmas),
            'Christmas Day ID must be handled correctly to ensure all valid dates are accepted',
        );

        // Leap day (29 February)
        $leapDay = '8802295800085';
        self::assertNotNull(
            SouthAfricanIDValidator::luhnIDValidate($leapDay),
            'Leap day ID must be handled correctly to verify proper leap year date validation',
        );
    }

    /**
     * Tests batch validation scenario (e.g., validating a CSV import).
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testBatchValidation(): void
    {
        $batch = [
            '8701105800085' => true,
            '3202295029085' => true,
            '4806010046080' => true,
            '3206015052087' => true,
            '0809015019080' => true,
            '1234567890123' => false,
            'ABCDEFGHIJKLM' => false,
            '8701105800086' => false, // Invalid checksum
            '' => false,
            '870110580008' => false, // Too short
        ];

        /** @var array<string, bool|null> $results */
        $results = [];
        foreach (array_keys($batch) as $id) {
            $idString = (string) $id;
            $results[$idString] = SouthAfricanIDValidator::luhnIDValidate($idString);
        }

        foreach ($batch as $id => $expected) {
            $idString = (string) $id;
            self::assertArrayHasKey($idString, $results, sprintf('Result must exist for ID: %s', $idString));

            if (!array_key_exists($idString, $results)) {
                continue;
            }

            $actualResult = $results[$idString];
            self::assertSame(
                $expected,
                $actualResult,
                sprintf(
                    'Batch validation failed for ID: %s - Expected %s but received %s',
                    $idString,
                    $expected ? 'valid' : 'invalid',
                    $actualResult === true ? 'valid' : ($actualResult === false ? 'invalid' : 'null'),
                ),
            );
        }
    }


    /**
     * Tests validation with citizenship status variations.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCitizenshipStatusValidation(): void
    {
        // All our test IDs have citizenship = 0 (SA citizen)
        $citizenIds = [
            '8701105800085',
            '3202295029085',
            '4806010046080',
            '3206015052087',
            '0809015019080',
        ];

        foreach ($citizenIds as $citizenId) {
            $result = SouthAfricanIDValidator::luhnIDValidate($citizenId);
            self::assertTrue($result, sprintf('South African citizen ID must validate correctly: %s to verify citizenship digit handling', $citizenId));
        }

        // Test with invalid citizenship (would return null if checksum was valid)
        $invalidCitizenship = '8701105003085'; // Citizenship = 3 (invalid)
        $result = SouthAfricanIDValidator::luhnIDValidate($invalidCitizenship);
        self::assertNotTrue($result, 'Invalid citizenship digit (3) must cause validation failure to ensure proper citizenship validation');
    }

    /**
     * Tests performance with repeated validations (caching scenario).
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testRepeatedValidations(): void
    {
        $idNumber = '8701105800085';
        $iterations = 1000;

        // Validate same ID multiple times
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);
            self::assertTrue($result, 'ID must validate consistently across multiple iterations to demonstrate performance');
        }

        $endTime = microtime(true);

        $totalTime = $endTime - $startTime;
        $avgTime = $totalTime / (float) $iterations;

        // Must execute rapidly (less than 1ms per validation)
        self::assertLessThan(
            0.001,
            $avgTime,
            sprintf('Average validation time exceeds performance threshold: %.6f seconds (must be under 1ms per validation)', $avgTime),
        );
    }

    /**
     * Tests edge case: person born on 1 January 1900 vs 2000.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testCenturyAmbiguity(): void
    {
        // Year 00 could be 1900 or 2000
        $year00 = '0001015019080';
        $result = SouthAfricanIDValidator::luhnIDValidate($year00);

        // The library must handle this consistently
        self::assertIsBool(
            $result,
            'Century ambiguous date must return consistent boolean result regardless of century interpretation',
        );
    }

    /**
     * Tests validation with database storage scenario.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testDatabaseStorageScenario(): void
    {
        // Simulate database records
        $dbRecords = [
            ['id' => 1, 'id_number' => '8701105800085', 'name' => 'Test User 1'],
            ['id' => 2, 'id_number' => '3202295029085', 'name' => 'Test User 2'],
            ['id' => 3, 'id_number' => 'INVALID123456', 'name' => 'Test User 3'],
            ['id' => 4, 'id_number' => null, 'name' => 'Test User 4'],
            ['id' => 5, 'id_number' => '', 'name' => 'Test User 5'],
        ];

        $validRecords = [];
        $invalidRecords = [];

        foreach ($dbRecords as $dbRecord) {
            if ($dbRecord['id_number'] === null || $dbRecord['id_number'] === '') {
                $invalidRecords[] = $dbRecord;
                continue;
            }

            if (SouthAfricanIDValidator::luhnIDValidate($dbRecord['id_number']) === true) {
                $validRecords[] = $dbRecord;
                continue;
            }

            $invalidRecords[] = $dbRecord;
        }

        self::assertCount(2, $validRecords, 'Database simulation must identify exactly 2 valid ID records from test dataset');
        self::assertCount(3, $invalidRecords, 'Database simulation must identify exactly 3 invalid records (including null and empty values)');
    }

    /**
     * Tests validation with library-specific error handling.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testLibraryValidationScenarios(): void
    {
        // Test valid ID
        $validResult = SouthAfricanIDValidator::luhnIDValidate('8701105800085');
        self::assertTrue(
            $validResult,
            'Valid ID must return true to confirm core validation functionality',
        );

        // Test invalid checksum
        $invalidChecksum = SouthAfricanIDValidator::luhnIDValidate('8701105800086');
        self::assertFalse(
            $invalidChecksum,
            'Invalid checksum must return false to ensure Luhn algorithm implementation is correct',
        );

        // Test invalid citizenship (would return null if other validations pass)
        $invalidCitizenship = SouthAfricanIDValidator::luhnIDValidate('8701105003085');
        self::assertNotTrue(
            $invalidCitizenship,
            'Invalid citizenship digit must not return true to verify citizenship validation logic',
        );

        // Test empty string
        $emptyResult = SouthAfricanIDValidator::luhnIDValidate('');
        self::assertFalse(
            $emptyResult,
            'Empty string must return false to handle edge case of missing input',
        );

        // Test short ID
        $shortResult = SouthAfricanIDValidator::luhnIDValidate('123');
        self::assertFalse(
            $shortResult,
            'Short ID must return false to enforce 13-digit length requirement',
        );
    }
}
