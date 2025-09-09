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
}
