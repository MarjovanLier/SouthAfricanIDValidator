<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Feature;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\GeneratorNotSupportedException;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Feature tests for bulk validation scenarios.
 * Tests the library's performance and accuracy when validating
 * large numbers of ID numbers, as would occur in batch imports
 * or data migration scenarios.
 */
final class BulkValidationTest extends TestCase
{
    /**
     * Tests bulk validation of multiple IDs.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testBulkIdValidation(): void
    {
        // Test data with mix of valid and invalid IDs
        $testIds = [
            '8701105800085', // Valid
            '3202295029085', // Valid
            '4806010046080', // Valid
            'INVALID123456', // Invalid - contains letters
            '',              // Invalid - empty
            '870110580008',  // Invalid - too short
        ];

        $validCount = 0;
        $invalidCount = 0;
        $errors = [];

        foreach ($testIds as $index => $idNumber) {
            if ($idNumber === '') {
                $invalidCount++;
                $errors[] = sprintf('Index %d: Empty ID number', $index);
                continue;
            }

            $result = SouthAfricanIDValidator::luhnIDValidate($idNumber);

            if ($result === true) {
                $validCount++;
                continue;
            }

            $invalidCount++;
            $errors[] = sprintf(
                'Index %d: Invalid ID number "%s"',
                $index,
                $idNumber,
            );
        }

        self::assertSame(3, $validCount, 'Must have 3 valid IDs');
        self::assertSame(3, $invalidCount, 'Must have 3 invalid IDs');
        self::assertCount(3, $errors, 'Must have 3 error messages');
    }

    /**
     * Tests performance with large dataset validation.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testLargeDatasetPerformance(): void
    {
        // Generate test dataset
        $validIds = [
            '8701105800085',
            '3202295029085',
            '4806010046080',
            '3206015052087',
            '0809015019080',
        ];

        /** @var list<string> $dataset */
        $dataset = [];

        // Create 10,000 records (mix of valid and invalid)
        for ($i = 0; $i < 10000; $i++) {
            if ($i % 3 === 0) {
                // Utilise a valid ID
                $dataset[] = $validIds[$i % count($validIds)];
                continue;
            }

            if ($i % 3 === 1) {
                // Generate invalid ID with wrong checksum
                $dataset[] = substr($validIds[$i % count($validIds)], 0, -1) . '9';
                continue;
            }

            // Generate completely invalid ID
            $dataset[] = str_pad((string) $i, 13, '0', STR_PAD_LEFT);
        }

        $startTime = microtime(true);
        $results = array_map(
            static fn(string $idNumber): ?bool => SouthAfricanIDValidator::luhnIDValidate($idNumber),
            $dataset,
        );
        $endTime = microtime(true);

        $validCount = count(array_filter($results, fn($result): bool => $result === true));
        $invalidCount = count($results) - $validCount;
        $totalTime = $endTime - $startTime;

        // Performance assertions
        self::assertLessThan(
            1.0,
            $totalTime,
            sprintf('Validation of 10,000 IDs took too long: %.3f seconds', $totalTime),
        );

        // Accuracy assertions
        self::assertGreaterThan(3000, $validCount, 'Should have ~3,333 valid IDs');
        self::assertLessThan(3500, $validCount, 'Should have ~3,333 valid IDs');
        self::assertGreaterThan(6500, $invalidCount, 'Should have ~6,667 invalid IDs');
        self::assertLessThan(7000, $invalidCount, 'Should have ~6,667 invalid IDs');
    }

    /**
     * Tests memory usage during bulk validation.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testMemoryUsageDuringBulkValidation(): void
    {
        $initialMemory = memory_get_usage();
        $peakMemory = $initialMemory;

        // Validate 1000 IDs and track memory
        for ($i = 0; $i < 1000; $i++) {
            $idNumber = str_pad((string) mt_rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT);
            SouthAfricanIDValidator::luhnIDValidate($idNumber);

            if ($i % 100 === 0) {
                $currentMemory = memory_get_usage();
                $peakMemory = max($peakMemory, $currentMemory);
            }
        }

        $memoryIncrease = $peakMemory - $initialMemory;

        // Memory should not increase significantly (less than 1MB)
        self::assertLessThan(
            1024 * 1024,
            $memoryIncrease,
            sprintf('Memory usage increased by %.2f MB', (float) $memoryIncrease / 1024.0 / 1024.0),
        );
    }

    /**
     * Tests validation with duplicate detection.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testDuplicateDetection(): void
    {
        $ids = [
            '8701105800085',
            '3202295029085',
            '8701105800085', // Duplicate
            '4806010046080',
            '3202295029085', // Duplicate
            '3206015052087',
            '8701105800085', // Duplicate
        ];

        $validated = [];
        $duplicates = [];

        foreach ($ids as $id) {
            if (isset($validated[$id])) {
                $duplicates[] = $id;
                continue;
            }

            $result = SouthAfricanIDValidator::luhnIDValidate($id);
            $validated[$id] = $result;
        }

        self::assertCount(4, $validated, 'Should have 4 unique IDs');
        self::assertCount(3, $duplicates, 'Should have 3 duplicates');
        self::assertSame(['8701105800085', '3202295029085', '8701105800085'], $duplicates);
    }

    /**
     * Tests parallel validation simulation.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testParallelValidationSimulation(): void
    {
        $chunks = [
            ['8701105800085', '3202295029085', '4806010046080'],
            ['3206015052087', '0809015019080', '1234567890123'],
            ['INVALID123456', '', '8701105800086'],
        ];

        $allResults = [];

        // Simulate parallel processing of chunks
        foreach ($chunks as $chunkIndex => $chunk) {
            $chunkResults = [];

            foreach ($chunk as $id) {
                $chunkResults[$id] = SouthAfricanIDValidator::luhnIDValidate($id);
            }

            $allResults['chunk_' . (string) $chunkIndex] = $chunkResults;
        }

        // Verify all chunks processed correctly
        self::assertCount(3, $allResults, 'Should have 3 chunks');

        // Count total valid/invalid
        $totalValid = 0;
        $totalInvalid = 0;

        foreach ($allResults as $allResult) {
            foreach ($allResult as $result) {
                if ($result === true) {
                    $totalValid++;
                    continue;
                }

                $totalInvalid++;
            }
        }

        self::assertSame(5, $totalValid, 'Should have 5 valid IDs total');
        self::assertSame(4, $totalInvalid, 'Should have 4 invalid IDs total');
    }

    /**
     * Tests validation with progress tracking.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testValidationWithProgressTracking(): void
    {
        $totalRecords = 100;
        /** @var non-empty-list<string> $validIds */
        $validIds = ['8701105800085', '3202295029085', '4806010046080'];

        $progress = $this->processRecordsWithProgressTracking($totalRecords, $validIds);

        $this->validateProgressCheckpoints($progress);
    }

    /**
     * Process records and track progress at checkpoints.
     *
     * @param int $totalRecords Total number of records to process
     * @param non-empty-list<string> $validIds List of valid ID numbers to use
     * @return list<array{percentage: float, processed: int, valid_so_far: int}> Progress data
     */
    private function processRecordsWithProgressTracking(int $totalRecords, array $validIds): array
    {
        /** @var list<array{percentage: float, processed: int, valid_so_far: int}> $progress */
        $progress = [];
        $checkpoints = [24, 49, 74, 99]; // 25%, 50%, 75%, 100%

        for ($i = 0; $i < $totalRecords; $i++) {
            $idNumber = $this->generateTestId($i, $validIds);
            SouthAfricanIDValidator::luhnIDValidate($idNumber);

            if (in_array($i, $checkpoints, true)) {
                $progress[] = $this->createProgressEntry($i, $totalRecords);
            }
        }

        return $progress;
    }

    /**
     * Generate a test ID number based on index.
     *
     * @param int $index Current iteration index
     * @param non-empty-list<string> $validIds List of valid ID numbers
     * @return string Generated ID number
     */
    private function generateTestId(int $index, array $validIds): string
    {
        // Use valid ID every 3rd record
        if ($index % 3 === 0) {
            $validIndex = $index % count($validIds);
            assert(isset($validIds[$validIndex]));
            return $validIds[$validIndex];
        }

        return 'INVALID' . (string) $index;
    }

    /**
     * Create a progress entry for the current checkpoint.
     *
     * @param int $currentIndex Current processing index
     * @param int $totalRecords Total number of records
     * @return array{percentage: float, processed: int, valid_so_far: int} Progress entry
     */
    private function createProgressEntry(int $currentIndex, int $totalRecords): array
    {
        $processed = $currentIndex + 1;
        $percentage = (float) $processed / (float) $totalRecords * 100.0;

        return [
            'percentage' => $percentage,
            'processed' => $processed,
            'valid_so_far' => $this->countValidInRange(0, $currentIndex),
        ];
    }

    /**
     * Validate that progress checkpoints are correct.
     *
     * @param list<array{percentage: float, processed: int, valid_so_far: int}> $progress Progress data
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws GeneratorNotSupportedException
     */
    private function validateProgressCheckpoints(array $progress): void
    {
        self::assertCount(4, $progress, 'Must have 4 progress checkpoints');

        $expectedPercentages = [25.0, 50.0, 75.0, 100.0];
        $checkpointNames = ['First', 'Second', 'Third', 'Final'];

        foreach ($expectedPercentages as $index => $expectedPercentage) {
            self::assertArrayHasKey($index, $progress, sprintf('Progress entry %s must exist', (string) $index));

            if (!isset($progress[$index])) {
                continue; // This should never happen after assertArrayHasKey
            }

            $progressEntry = $progress[$index];
            self::assertArrayHasKey('percentage', $progressEntry, "Progress entry must have percentage");
            self::assertSame(
                $expectedPercentage,
                $progressEntry['percentage'],
                sprintf('%s checkpoint must be at %s%%', $checkpointNames[$index], (string) $expectedPercentage),
            );
        }
    }

    /**
     * Helper method to count valid IDs in a range.
     *
     * @psalm-return int<0, max>
     */
    private function countValidInRange(int $start, int $end): int
    {
        $count = 0;
        for ($i = $start; $i <= $end; $i++) {
            if ($i % 3 === 0) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Tests error recovery during bulk validation.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testErrorRecoveryDuringBulkValidation(): void
    {
        $dataset = [
            '8701105800085',
            null, // Null value
            '3202295029085',
            '', // Empty string
            '4806010046080',
            false, // Boolean
            '3206015052087',
            [], // Array
            '0809015019080',
        ];

        $results = [];
        $errors = [];

        foreach ($dataset as $index => $id) {
            try {
                if (!is_string($id)) {
                    throw new TypeError(sprintf(
                        'ID must be string, %s given at index %d',
                        gettype($id),
                        $index,
                    ));
                }

                $results[$index] = SouthAfricanIDValidator::luhnIDValidate($id);
            } catch (\TypeError $e) {
                $errors[$index] = $e->getMessage();
                $results[$index] = false;
            }
        }

        self::assertCount(9, $results, 'Should process all records');
        self::assertCount(3, $errors, 'Should catch 3 type errors');

        // Count successful validations
        $validCount = count(array_filter(
            $results,
            fn($result, $index): bool => $result === true && !isset($errors[$index]),
            ARRAY_FILTER_USE_BOTH,
        ));

        self::assertSame(5, $validCount, 'Should have 5 valid IDs');
    }
}
