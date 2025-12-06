<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the edge case input handling in SouthAfricanIDValidator.
 */
#[CoversMethod(SouthAfricanIDValidator::class, 'luhnIDValidate')]
final class EdgeCaseInputTest extends TestCase
{
    /**
     * Provides various edge case inputs.
     *
     * @return array<string, array{string, false}>
     */
    public static function provideEdgeCaseInputs(): array
    {
        return [
            'empty string' => ['', false],
            'single space' => [' ', false],
            'multiple spaces' => ['     ', false],
            'tab character' => ["\t", false],
            'newline' => ["\n", false],
            'mixed whitespace' => [" \t\n\r ", false],
            'single digit' => ['1', false],
            'two digits' => ['12', false],
            'twelve digits' => ['123456789012', false],
            'fourteen digits' => ['12345678901234', false],
            'very long number' => [str_repeat('1', 100), false],
            'zero string' => ['0', false],
            'thirteen zeros' => ['0000000000000', false],
            'thirteen spaces' => ['             ', false],
            'special chars only' => ['!@#$%^&*()_+-=', false],
            'unicode digits' => ['႑႒႓႔႕႖႗႘႙႐႑႒႓', false], // Myanmar digits
            'mixed unicode' => ['1234567890ñ23', false],
            'null character' => ["\0", false],
            'all null chars' => [str_repeat("\0", 13), false],
        ];
    }

    /**
     * Tests that edge case inputs are handled correctly.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('provideEdgeCaseInputs')]
    public function testEdgeCaseInputs(string $input, bool $expected): void
    {
        $result = SouthAfricanIDValidator::luhnIDValidate($input);
        $encoded = json_encode($input);
        self::assertSame($expected, $result, sprintf('Edge case input should be handled: %s', $encoded !== false ? $encoded : 'encoding error'));
    }

    /**
     * Tests whitespace handling with valid IDs.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testWhitespaceWithValidId(): void
    {
        $validId = '8701105800085';

        // Leading whitespace
        $result = SouthAfricanIDValidator::luhnIDValidate('   ' . $validId);
        self::assertTrue($result, 'Valid ID with leading whitespace should be accepted after sanitisation');

        // Trailing whitespace
        $result = SouthAfricanIDValidator::luhnIDValidate($validId . '   ');
        self::assertTrue($result, 'Valid ID with trailing whitespace should be accepted after sanitisation');

        // Both
        $result = SouthAfricanIDValidator::luhnIDValidate('  ' . $validId . '  ');
        self::assertTrue($result, 'Valid ID with surrounding whitespace should be accepted after sanitisation');

        // Interspersed whitespace
        $result = SouthAfricanIDValidator::luhnIDValidate('8701 1058 0008 5');
        self::assertTrue($result, 'Valid ID with spaces should be accepted after sanitisation');
    }

    /**
     * Tests extremely long inputs.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testExtremelyLongInputs(): void
    {
        // Very long string that contains a valid ID
        $validId = '8701105800085';
        $longPrefix = str_repeat('9', 1000);
        $longSuffix = str_repeat('0', 1000);

        $result = SouthAfricanIDValidator::luhnIDValidate($longPrefix . $validId . $longSuffix);
        self::assertFalse($result, 'Extremely long input should be invalid');

        // Exactly 13 digits buried in non-numeric characters
        $mixed = 'abc' . $validId . 'xyz';
        $result = SouthAfricanIDValidator::luhnIDValidate($mixed);
        self::assertTrue($result, 'Valid ID within non-numeric characters should be extracted and validated');
    }

    /**
     * Tests inputs with only non-numeric characters.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testNonNumericOnly(): void
    {
        $inputs = [
            'abcdefghijklm',
            'ABCDEFGHIJKLM',
            '!@#$%^&*()_+-',
            'Hello World!!',
            '.............',
            '-------------',
        ];

        foreach ($inputs as $input) {
            $result = SouthAfricanIDValidator::luhnIDValidate($input);
            self::assertFalse($result, 'Non-numeric input should be invalid: ' . $input);
        }
    }

    /**
     * Tests boundary length cases.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testBoundaryLengths(): void
    {
        // Test lengths from 0 to 20
        for ($i = 0; $i <= 20; $i++) {
            if ($i === 13) {
                continue; // Skip valid length
            }

            $number = str_repeat('1', $i);
            $result = SouthAfricanIDValidator::luhnIDValidate($number);
            self::assertFalse($result, sprintf('Length %d should be invalid', $i));
        }
    }

    /**
     * Tests mixed valid and invalid characters.
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function testMixedCharacters(): void
    {
        // Valid ID with each digit replaced by a letter
        $validId = '8701105800085';

        for ($i = 0; $i < 13; $i++) {
            $mixed = $validId;
            $mixed[$i] = 'X';
            $result = SouthAfricanIDValidator::luhnIDValidate($mixed);
            self::assertFalse($result, sprintf('ID with letter at position %d should be invalid', $i));
        }
    }
}
