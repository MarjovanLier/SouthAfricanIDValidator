<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Feature;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use MattSparks\BLNS\BLNS;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validator against the Big List of Naughty Strings.
 * Ensures the validator can handle malicious, unusual, and edge-case inputs safely.
 */
final class NaughtyStringsTest extends TestCase
{
    /**
     * Tests validation against all naughty strings.
     * None of these strings should cause exceptions or errors.
     * All should return false (invalid) or null (invalid citizenship).
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testNaughtyStringsValidation(): void
    {
        foreach ((new BLNS())->getList() as $naughtyString) {
            // Skip non-string values
            if (!is_string($naughtyString)) {
                continue;
            }

            // The validator should handle any input gracefully
            $result = SouthAfricanIDValidator::luhnIDValidate($naughtyString);

            // Result should be false or null, never true for naughty strings
            self::assertNotTrue(
                $result,
                sprintf(
                    'Naughty string must not validate as true: %s',
                    $this->safeJsonEncode($naughtyString, JSON_UNESCAPED_UNICODE),
                ),
            );

            // Ensure no exceptions are thrown
            self::assertContains(
                $result,
                [false, null],
                sprintf(
                    'Naughty string must return false or null: %s',
                    $this->safeJsonEncode($naughtyString, JSON_UNESCAPED_UNICODE),
                ),
            );
        }
    }

    /**
     * Safely encode a value to JSON for display in error messages.
     *
     * @throws \JsonException
     */
    private function safeJsonEncode(string $value, int $flags = 0): string
    {
        $encoded = json_encode($value, JSON_THROW_ON_ERROR | $flags);

        return $encoded !== false ? $encoded : 'encoding failed';
    }

    /**
     * Tests specific categories of naughty strings that are relevant to ID validation.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testSpecificNaughtyStringCategories(): void
    {
        // Test SQL injection attempts
        $sqlInjectionStrings = [
            "1'; DROP TABLE users--",
            "' OR '1'='1",
            "1' OR '1' = '1",
            '1"; DROP TABLE users--',
            "1' OR 1=1--",
            "admin'--",
            "1' UNION SELECT NULL--",
        ];

        foreach ($sqlInjectionStrings as $sqlInjectionString) {
            $result = SouthAfricanIDValidator::luhnIDValidate($sqlInjectionString);
            self::assertFalse(
                $result,
                sprintf('SQL injection string must return false: %s', $sqlInjectionString),
            );
        }

        // Test numeric edge cases
        $numericEdgeCases = [
            (string) PHP_INT_MAX,
            (string) PHP_INT_MIN,
            '999999999999999999999999999999',
            '-1234567890123',
            '1234567890123.0',
            '1,234,567,890,123',
            '١٢٣٤٥٦٧٨٩٠١٢٣', // Arabic numerals
            '12345678901२३', // Mixed with Devanagari
        ];

        foreach ($numericEdgeCases as $numericEdgeCase) {
            $result = SouthAfricanIDValidator::luhnIDValidate($numericEdgeCase);
            self::assertNotTrue(
                $result,
                sprintf('Numeric edge case must not validate as true: %s', $numericEdgeCase),
            );
        }

        // Test Unicode and special characters
        $unicodeStrings = [
            '🎉🎊🎈🎁🎂🍰🎃🎄',
            '田中さんにあげて下さい',
            'ЁЂЃЄЅІЇЈЉЊЋЌЍЎЏАБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдежзийклмнопрстуфхцчшщъыьэюя',
            '󠀀󠀁󠀂󠀃󠀄󠀅󠀆󠀇󠀈󠀉',
            "\u200B\u200C\u200D\uFEFF", // Zero-width characters
        ];

        foreach ($unicodeStrings as $unicodeString) {
            $result = SouthAfricanIDValidator::luhnIDValidate($unicodeString);
            self::assertFalse(
                $result,
                sprintf('Unicode string must return false: %s', $this->safeJsonEncode($unicodeString)),
            );
        }

        // Test strings that might cause parsing issues
        $parsingEdgeCases = [
            str_repeat('1', 1000000), // Very long string
            '', // Empty string
            ' ', // Single space
            "\n\r\t", // Whitespace characters
            "\0\0\0\0\0\0\0\0\0\0\0\0\0", // Null bytes
            "\\\\\\\\\\\\\\\\\\\\\\\\\\", // Backslashes
        ];

        foreach ($parsingEdgeCases as $parsingEdgeCase) {
            $result = SouthAfricanIDValidator::luhnIDValidate($parsingEdgeCase);
            self::assertNotTrue(
                $result,
                sprintf(
                    'Parsing edge case must not validate as true: %s',
                    strlen($parsingEdgeCase) > 50 ? 'string of length ' . (string) strlen($parsingEdgeCase) : $this->safeJsonEncode($parsingEdgeCase),
                ),
            );
        }
    }

    /**
     * Tests that valid IDs mixed with naughty characters are handled correctly.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testValidIDsWithNaughtyCharacters(): void
    {
        $validId = '8701105800085';

        // Test valid ID with naughty prefixes that contain only non-numeric chars
        // These should sanitise to the valid ID and thus return true
        $nonNumericPrefixes = [
            "'; DROP TABLE--" . $validId,
            "\0\0\0" . $validId,
            "🎉🎊🎈" . $validId,
        ];

        foreach ($nonNumericPrefixes as $nonNumericPrefix) {
            $result = SouthAfricanIDValidator::luhnIDValidate($nonNumericPrefix);
            self::assertTrue(
                $result,
                sprintf('Valid ID with non-numeric prefix should sanitise and validate as true: %s', $this->safeJsonEncode($nonNumericPrefix)),
            );
        }

        // Test valid ID with numeric prefixes that make it too long
        $numericPrefixTests = [
            '123' . $validId,
            '999' . $validId,
            (string) PHP_INT_MAX . $validId,
        ];

        foreach ($numericPrefixTests as $numericPrefixTest) {
            $result = SouthAfricanIDValidator::luhnIDValidate($numericPrefixTest);
            self::assertFalse(
                $result,
                sprintf('Valid ID with numeric prefix must return false due to length: %s', substr($numericPrefixTest, 0, 50)),
            );
        }

        // Test valid ID with non-numeric suffixes
        // These should sanitise to the valid ID and thus return true
        $nonNumericSuffixes = [
            $validId . "'; DROP TABLE--",
            $validId . "\0\0\0",
            $validId . "🎉🎊🎈",
        ];

        foreach ($nonNumericSuffixes as $nonNumericSuffix) {
            $result = SouthAfricanIDValidator::luhnIDValidate($nonNumericSuffix);
            self::assertTrue(
                $result,
                sprintf('Valid ID with non-numeric suffix should sanitise and validate as true: %s', $this->safeJsonEncode($nonNumericSuffix)),
            );
        }

        // Test valid ID with numeric suffixes that make it too long
        $numericSuffixTests = [
            $validId . '123',
            $validId . '999',
            $validId . (string) PHP_INT_MAX,
        ];

        foreach ($numericSuffixTests as $numericSuffixTest) {
            $result = SouthAfricanIDValidator::luhnIDValidate($numericSuffixTest);
            self::assertFalse(
                $result,
                sprintf('Valid ID with numeric suffix must return false due to length: %s', substr($numericSuffixTest, 0, 50)),
            );
        }

        // Test valid ID with non-numeric characters interspersed
        // These should sanitise to the valid ID and return true
        $interspersedTests = [
            '8701 105800085',
            "8701'105800085",
            '8701' . "🎉" . '105800085',
            '8701' . PHP_EOL . '105800085',
        ];

        foreach ($interspersedTests as $interspersedId) {
            $result = SouthAfricanIDValidator::luhnIDValidate($interspersedId);
            self::assertTrue(
                $result,
                sprintf('Valid ID with non-numeric characters interspersed should sanitise and validate: %s', $this->safeJsonEncode($interspersedId)),
            );
        }

        // Test valid ID with numeric characters interspersed that corrupt the ID
        $numericInterspersed = [
            '87011905800085', // Extra digit makes it 14 chars
            '870199105800085', // Extra digits make it 15 chars
            '87001105800085',  // Extra digit in date corrupts checksum
        ];

        foreach ($numericInterspersed as $interspersedId) {
            $result = SouthAfricanIDValidator::luhnIDValidate($interspersedId);
            self::assertFalse(
                $result,
                sprintf('Valid ID with numeric characters interspersed must return false: %s', $interspersedId),
            );
        }
    }
}
