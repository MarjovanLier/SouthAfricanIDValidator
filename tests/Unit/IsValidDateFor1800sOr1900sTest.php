<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidDateFor1800sOr1900s
 */
final class IsValidDateFor1800sOr1900sTest extends TestCase
{
    /**
     * Provides a set of dates along with expected results to test
     * if they belong to the 18th century.
     *
     * @return array<array{0: string, 1: bool}>
     *
     * @psalm-return list{list{'990101', true}, list{'010101', true}, list{'990230', false}, list{'000230', false},
     *     list{'010299', false}, list{'01013', false}, list{'0110111', false}, list{'019999', false}, list{'991231',
     *     true}, list{'000229', false}}
     */
    public static function provideDatesFor1800sOr1900s(): array
    {
        return [
            [
                '990101',
                true,
            ],
            [
                '010101',
                true,
            ],
            [
                '990230',
                false,
            ],
            [
                '000230',
                false,
            ],
            [
                '010299',
                false,
            ],
            [
                '01013',
                false,
            ],
            [
                '0110111',
                false,
            ],
            [
                '019999',
                false,
            ],
            [
                '991231',
                true,
            ],
            [
                '000229',
                false,
            ],
        ];
    }


    /**
     * Tests the `isValidDateFor1800sOr1900s` method using the provided dates from the
     * data provider.
     * This method uses reflection to access the private method.
     *
     * @param string $date The date string to test.
     * @param bool $expected The expected result.
     *
     * @dataProvider provideDatesFor1800sOr1900s
     */
    #[DataProvider('provideDatesFor1800sOr1900s')]
    public function testIsValid18Date(string $date, bool $expected): void
    {
        // Use reflection to access and invoke the private static method `isValidDateFor1800sOr1900s`.
        // The first argument to `invoke` is `null` because the method is static.
        /**
         * @var bool $result
         */
        $result = $this->getPrivateMethod()->invoke(null, $date);

        self::assertEquals($expected, $result);
    }


    /**
     * Uses reflection to access the private `isValidDateFor1800sOr1900s` method
     * from the `SouthAfricanIDValidator` class for testing purposes.
     *
     * @return ReflectionMethod The reflected `isValidDateFor1800sOr1900s` method.
     */
    private function getPrivateMethod(): ReflectionMethod
    {
        $reflectionMethod = (new ReflectionClass(SouthAfricanIDValidator::class))->getMethod(
            'isValidDateFor1800sOr1900s',
        );

        /**
         * @noinspection PhpExpressionResultUnusedInspection
         *
         * @psalm-suppress UnusedMethodCall
         */
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }
}
