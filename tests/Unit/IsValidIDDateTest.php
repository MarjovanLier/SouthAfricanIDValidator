<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidIDDate
 */
final class IsValidIDDateTest extends TestCase
{
    /**
     * Provides a set of valid ID date portions.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'880101'}, list{'990101'}, list{'000229'}, list{'010101'}, list{'181229'},
     *     list{'200229'}}
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
     * Provides a set of invalid ID date portions.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'870230'}, list{'990230'}, list{'000230'}, list{'010299'}, list{'01013'},
     *     list{'0110111'}, list{'170229'}, list{'180229'}, list{'190229'}}
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
        ];
    }


    /**
     * @dataProvider provideValidIDDates
     */
    #[DataProvider('provideValidIDDates')]
    public function testValidIdDates(string $date): void
    {
        self::assertTrue(SouthAfricanIDValidator::isValidIDDate($date));
    }


    /**
     * @dataProvider provideInvalidIDDates
     */
    #[DataProvider('provideInvalidIDDates')]
    public function testInvalidIdDates(string $date): void
    {
        self::assertFalse(SouthAfricanIDValidator::isValidIDDate($date));
    }
}
