<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversMethod(SouthAfricanIDValidator::class, 'sanitiseNumber')]
final class SanitiseNumberTest extends TestCase
{
    /**
     * @throws ExpectationFailedException
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testSanitiseNumber(): void
    {
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', ['123-456']);
        self::assertEquals(
            '123456',
            $result,
            "Must remove hyphens from '123-456' and return '123456'",
        );

        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', ['abc123']);
        self::assertEquals(
            '123',
            $result,
            "Must remove letters from 'abc123' and return only digits '123'",
        );

        // Test for already clean number (all digits)
        /**
         * @var string $result
         */
        $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', ['123456']);
        self::assertEquals(
            '123456',
            $result,
            "Already clean number '123456' must remain unchanged",
        );
    }

    /**
     * Tests sanitisation of invisible Unicode characters.
     *
     * Verifies that invisible characters like zero-width joiners, RTL/LTR marks,
     * and other Unicode formatting characters are properly removed.
     */
    public function testSanitiseInvisibleUnicodeCharacters(): void
    {
        // Test with various invisible Unicode characters that can be copy-pasted
        $testCases = [
            // Zero-width characters
            "8701\u{200B}105\u{200C}800\u{200D}085" => '8701105800085', // Zero-width space, ZWNJ, ZWJ
            // RTL/LTR marks
            "8701\u{202A}105\u{202B}800085" => '8701105800085', // Left-to-right embedding, right-to-left embedding
            // Combining characters
            "870110\u{0301}5800085" => '8701105800085', // Combining acute accent
            // All noise (no digits)
            "\u{200B}\u{200C}\u{200D}" => '', // Only invisible characters
        ];

        foreach ($testCases as $input => $expected) {
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', [$input]);
            self::assertSame($expected, $result, sprintf('Failed to sanitise: %s', json_encode($input)));
        }
    }

    /**
     * Tests sanitisation with all non-digit input.
     *
     * Ensures that input containing only noise returns empty string, not null.
     */
    public function testSanitiseNumberAllNoise(): void
    {
        $allNoiseInputs = [
            '!!!',
            'ABCDEF',
            '---***',
            '   ',
            '',
        ];

        foreach ($allNoiseInputs as $input) {
            $result = $this->invokeMethod(new SouthAfricanIDValidator(), 'sanitiseNumber', [$input]);
            self::assertSame('', $result, sprintf('All-noise input "%s" should return empty string', $input));
        }
    }


    /**
     * Invokes a protected or private method of a class.
     *
     * @param object $object Instantiated object on which to invoke the method.
     * @param string $methodName Method name to invoke.
     * @param array<int, string> $parameters Array of parameters to pass to the method.
     *
     * @return mixed Method return value.
     *
     * @throws \ReflectionException
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflectionMethod = (new ReflectionClass($object::class))->getMethod($methodName);

        /**
         * @noinspection   PhpExpressionResultUnusedInspection
         * @psalm-suppress UnusedMethodCall
         */

        return $reflectionMethod->invokeArgs($object, $parameters);
    }
}
