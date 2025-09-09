<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit tests for the batchValidate method in SouthAfricanIDValidator.
 */
final class BatchValidateTest extends TestCase
{
    /**
     * Tests batchValidate with all valid IDs.
     */
    public function testBatchValidateWithAllValidIds(): void
    {
        $idNumbers = [
            '8001015009087', // Valid modern male citizen
            '8001015009095', // Valid modern with indicator 9
            '8001015009004', // Valid legacy
        ];

        $results = SouthAfricanIDValidator::batchValidate($idNumbers);

        $this->assertCount(3, $results, 'Should return 3 results');
        $this->assertArrayHasKey('8001015009087', $results, 'First ID should be in results');
        $this->assertArrayHasKey('8001015009095', $results, 'Second ID should be in results');
        $this->assertArrayHasKey('8001015009004', $results, 'Third ID should be in results');
        $this->assertTrue(isset($results['8001015009087']) && $results['8001015009087'] === true, 'First ID should be valid');
        $this->assertTrue(isset($results['8001015009095']) && $results['8001015009095'] === true, 'Second ID should be valid');
        $this->assertTrue(isset($results['8001015009004']) && $results['8001015009004'] === true, 'Third ID should be valid');
    }

    /**
     * Tests batchValidate with mixed valid and invalid IDs.
     */
    public function testBatchValidateWithMixedIds(): void
    {
        $idNumbers = [
            '8001015009087', // Valid
            '1234567890123', // Invalid checksum
            '800101500908',  // Too short
            '8001015009095', // Valid
        ];

        $results = SouthAfricanIDValidator::batchValidate($idNumbers);

        $this->assertCount(4, $results, 'Should return 4 results');
        $this->assertArrayHasKey('8001015009087', $results, 'First ID should be in results');
        $this->assertTrue(isset($results['8001015009087']) && $results['8001015009087'] === true, 'First ID should be valid');
        $this->assertArrayHasKey('1234567890123', $results, 'Second ID should be in results');
        $this->assertFalse(isset($results['1234567890123']) && $results['1234567890123'] === true, 'Second ID should be invalid');
        $this->assertArrayHasKey('800101500908', $results, 'Third ID should be in results');
        $this->assertFalse($results['800101500908'] === true, 'Third ID should be invalid');
        $this->assertArrayHasKey('8001015009095', $results, 'Fourth ID should be in results');
        $this->assertTrue($results['8001015009095'] === true, 'Fourth ID should be valid');
    }

    /**
     * Tests batchValidate with IDs containing invalid citizenship.
     */
    public function testBatchValidateWithInvalidCitizenship(): void
    {
        $idNumbers = [
            '8001015009087', // Valid (citizenship 0)
            '8001015009387', // Invalid citizenship (3)
        ];

        $results = SouthAfricanIDValidator::batchValidate($idNumbers);

        $this->assertArrayHasKey('8001015009087', $results, 'Valid citizenship ID should be in results');
        $this->assertTrue(isset($results['8001015009087']) && $results['8001015009087'] === true, 'Valid citizenship ID should pass');
        $this->assertArrayHasKey('8001015009387', $results, 'Invalid citizenship ID should be in results');
        $this->assertNull($results['8001015009387'], 'Invalid citizenship should return null');
    }

    /**
     * Tests batchValidate with empty array.
     */
    public function testBatchValidateWithEmptyArray(): void
    {
        $results = SouthAfricanIDValidator::batchValidate([]);

        $this->assertCount(0, $results, 'Should have no results');
    }

    /**
     * Tests batchValidate with duplicate IDs in input.
     */
    public function testBatchValidateWithDuplicateIds(): void
    {
        $idNumbers = [
            '8001015009087',
            '8001015009087', // Duplicate
            '8001015009095',
        ];

        $results = SouthAfricanIDValidator::batchValidate($idNumbers);

        $this->assertCount(2, $results, 'Should have 2 unique results');
        $this->assertArrayHasKey('8001015009087', $results, 'Duplicate ID should be in results');
        $this->assertTrue(isset($results['8001015009087']) && $results['8001015009087'] === true, 'Duplicate ID should be validated once');
        $this->assertArrayHasKey('8001015009095', $results, 'Other ID should be in results');
        $this->assertTrue(isset($results['8001015009095']) && $results['8001015009095'] === true, 'Other ID should be validated');
    }

    /**
     * Tests batchValidate with non-string values in array.
     */
    public function testBatchValidateWithNonStringValues(): void
    {
        /** @var array<int|string, string> $validIds */
        $validIds = [
            '8001015009087',
            '8001015009095',
        ];

        // Test that batchValidate filters non-string values internally
        // Since batchValidate expects array<string>, we test with valid strings only
        $results = SouthAfricanIDValidator::batchValidate($validIds);

        $this->assertCount(2, $results, 'Should process both valid IDs');
        $this->assertArrayHasKey('8001015009087', $results, 'First ID should be processed');
        $this->assertArrayHasKey('8001015009095', $results, 'Second ID should be processed');
    }

    /**
     * Tests that batchValidate handles mixed types internally.
     * This test uses reflection to test the internal behaviour without PHPStan issues.
     */
    public function testBatchValidateHandlesMixedTypesInternally(): void
    {
        // Use reflection to bypass type checking and test the actual runtime behaviour
        $reflectionMethod = new ReflectionMethod(SouthAfricanIDValidator::class, 'batchValidate');

        // Create an array with mixed types that would be filtered at runtime
        $mixedArray = [
            '8001015009087',  // Valid string
            123,              // Integer - will be skipped
            '8001015009095',  // Valid string
        ];

        // Invoke with mixed array to test the continue statement
        /** @var array<string, bool|null> $results */
        $results = $reflectionMethod->invoke(null, $mixedArray);

        // Should only process string values
        $this->assertCount(2, $results, 'Should only process string values');
        $this->assertArrayHasKey('8001015009087', $results, 'First string should be processed');
        $this->assertArrayHasKey('8001015009095', $results, 'Second string should be processed');
    }

    /**
     * Tests batchValidate preserves original ID format in keys.
     */
    public function testBatchValidatePreservesOriginalFormat(): void
    {
        $idNumbers = [
            '80-01-01 5009-087', // Formatted version
            '8001015009095',      // Unformatted
        ];

        $results = SouthAfricanIDValidator::batchValidate($idNumbers);

        $this->assertArrayHasKey('80-01-01 5009-087', $results, 'Should preserve formatted key');
        $this->assertArrayHasKey('8001015009095', $results, 'Should preserve unformatted key');
        $this->assertNotNull($results['80-01-01 5009-087'] ?? null, 'Formatted ID should have result');
        $this->assertTrue(isset($results['80-01-01 5009-087']) && $results['80-01-01 5009-087'], 'Formatted ID should be valid');
        $this->assertNotNull($results['8001015009095'] ?? null, 'Unformatted ID should have result');
        $this->assertTrue(isset($results['8001015009095']) && $results['8001015009095'] === true, 'Unformatted ID should be valid');
    }
}
