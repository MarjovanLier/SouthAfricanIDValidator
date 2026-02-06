<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Exception;
use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(SouthAfricanIDValidator::class, 'isValidIDDate')]
final class IsValidIDDateTest extends TestCase
{
    /**
     * Provides valid ID date portions for testing.
     *
     * @return array<array<string>>
     */
    public static function provideValidIDDates(): array
    {
        return [
            ['880101'],
            ['990101'],
            ['000229'],
            ['010101'],
            ['181229'],
            ['200229'],
        ];
    }


    /**
     * Provides invalid ID date portions for testing.
     *
     * @return array<array<string>>
     */
    public static function provideInvalidIDDates(): array
    {
        return [
            ['870230'],
            ['990230'],
            ['000230'],
            ['010299'],
            ['01013'],
            ['0110111'],
            ['170229'],
            ['180229'],
            ['190229'],
            ['87023a'], // Contains non-digit character
            ['a80101'], // Contains non-digit character
        ];
    }


    /**
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('provideValidIDDates')]
    public function testValidIdDates(string $date): void
    {
        $year = substr($date, 0, 2);
        $month = substr($date, 2, 2);
        $day = substr($date, 4, 2);

        self::assertTrue(
            SouthAfricanIDValidator::isValidIDDate($date),
            sprintf('Valid date must pass validation: %s (YY=%s, MM=%s, DD=%s)', $date, $year, $month, $day),
        );
    }


    /**
     * @throws ExpectationFailedException
     * @throws Exception
     */
    #[DataProvider('provideInvalidIDDates')]
    public function testInvalidIdDates(string $date): void
    {
        $year = substr($date, 0, 2);
        $month = substr($date, 2, 2);
        $day = substr($date, 4, 2);

        self::assertFalse(
            SouthAfricanIDValidator::isValidIDDate($date),
            sprintf('Invalid date must fail validation: %s (YY=%s, MM=%s, DD=%s)', $date, $year, $month, $day),
        );
    }
}
