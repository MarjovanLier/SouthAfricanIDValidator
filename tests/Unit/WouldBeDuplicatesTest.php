<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the wouldBeDuplicates method in SouthAfricanIDValidator.
 */
final class WouldBeDuplicatesTest extends TestCase
{
    /**
     * Tests wouldBeDuplicates with IDs that share first 11 digits.
     */
    public function testWouldBeDuplicatesWithMatchingFirst11Digits(): void
    {
        // Same first 11 digits, different race indicators and checksums
        $id1 = '8001015009087'; // ...08 with checksum 7
        $id2 = '8001015009095'; // ...09 with checksum 5

        $result = SouthAfricanIDValidator::wouldBeDuplicates($id1, $id2);

        $this->assertTrue($result, 'IDs with same first 11 digits should be duplicates');
    }

    /**
     * Tests wouldBeDuplicates with IDs that differ in date.
     */
    public function testWouldBeDuplicatesWithDifferentDates(): void
    {
        $id1 = '8001015009087'; // Date: 800101
        $id2 = '8001025009085'; // Date: 800102 (different day)

        $result = SouthAfricanIDValidator::wouldBeDuplicates($id1, $id2);

        $this->assertFalse($result, 'IDs with different dates should not be duplicates');
    }

    /**
     * Tests wouldBeDuplicates with IDs that differ in sequence number.
     */
    public function testWouldBeDuplicatesWithDifferentSequence(): void
    {
        $id1 = '8001015009087'; // Sequence: 5009
        $id2 = '8001015010084'; // Sequence: 5010

        $result = SouthAfricanIDValidator::wouldBeDuplicates($id1, $id2);

        $this->assertFalse($result, 'IDs with different sequence numbers should not be duplicates');
    }

    /**
     * Tests wouldBeDuplicates with IDs that differ in citizenship.
     */
    public function testWouldBeDuplicatesWithDifferentCitizenship(): void
    {
        $id1 = '8001015009087'; // Citizenship: 0
        $id2 = '8001015009186'; // Citizenship: 1

        $result = SouthAfricanIDValidator::wouldBeDuplicates($id1, $id2);

        $this->assertFalse($result, 'IDs with different citizenship should not be duplicates');
    }

    /**
     * Tests wouldBeDuplicates with identical IDs.
     */
    public function testWouldBeDuplicatesWithIdenticalIds(): void
    {
        $idNumber = '8001015009087';

        $result = SouthAfricanIDValidator::wouldBeDuplicates($idNumber, $idNumber);

        $this->assertTrue($result, 'Identical IDs should be considered duplicates');
    }

    /**
     * Tests wouldBeDuplicates with invalid length IDs.
     */
    public function testWouldBeDuplicatesWithInvalidLength(): void
    {
        $id1 = '800101500908'; // Too short
        $id2 = '8001015009087';

        $result = SouthAfricanIDValidator::wouldBeDuplicates($id1, $id2);

        $this->assertFalse($result, 'Should return false when any ID has invalid length');

        // Both invalid
        $result = SouthAfricanIDValidator::wouldBeDuplicates('123', '456');
        $this->assertFalse($result, 'Should return false when both IDs have invalid length');
    }

    /**
     * Tests wouldBeDuplicates with formatted IDs.
     */
    public function testWouldBeDuplicatesWithFormattedIds(): void
    {
        $id1 = '80-01-01 5009-087'; // Formatted
        $id2 = '8001015009095';      // Unformatted, same first 11 digits

        $result = SouthAfricanIDValidator::wouldBeDuplicates($id1, $id2);

        $this->assertTrue($result, 'Should handle formatted IDs correctly');
    }

    /**
     * Tests wouldBeDuplicates with legacy and modern IDs.
     */
    public function testWouldBeDuplicatesWithLegacyAndModern(): void
    {
        // Same first 11 digits, one legacy (0) and one modern (8)
        $legacyId = '8001015009004'; // Race indicator 0
        $modernId = '8001015009087'; // Race indicator 8

        $result = SouthAfricanIDValidator::wouldBeDuplicates($legacyId, $modernId);

        $this->assertTrue($result, 'Legacy and modern IDs with same first 11 digits should be duplicates');
    }

    /**
     * Tests the actual use case: when digit 9 would be needed.
     */
    public function testWouldBeDuplicatesRealUseCase(): void
    {
        // Two people born on same day, same sequence, same citizenship
        // This would require one to use digit 9
        $person1 = '8001015009087'; // Uses digit 8
        $person2 = '8001015009095'; // Would need to use digit 9

        $result = SouthAfricanIDValidator::wouldBeDuplicates($person1, $person2);

        $this->assertTrue($result, 'These IDs would be duplicates without different race indicators');

        // Verify they have same first 11 digits
        $this->assertSame(
            substr($person1, 0, 11),
            substr($person2, 0, 11),
            'First 11 digits should match',
        );
    }
}
