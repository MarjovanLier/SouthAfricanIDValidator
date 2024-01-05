<?php

declare(strict_types=1);

namespace MarjovanLier\SouthAfricanIDValidator\Tests\Unit;

use MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 *
 * @covers \MarjovanLier\SouthAfricanIDValidator\SouthAfricanIDValidator::isValidLuhnChecksum
 */
final class IsValidLuhnChecksumTest extends TestCase
{
    /**
     * Provides a set of valid Luhn numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'79927398713'}, list{'1234567812345670'}, list{'0'}, list{'18'}, list{'26'}, list{'34'},
     *     list{'42'}, list{'59'}, list{'67'}, list{'75'}, list{'83'}, list{'91'}, list{'109'}, list{'117'},
     *     list{'125'}, list{'133'}, list{'141'}, list{'158'}, list{'166'}, list{'174'}, list{'182'}, list{'190'}}
     */
    public static function provideValidLuhnNumbers(): array
    {
        return [
            ['79927398713'],
            ['1234567812345670'],
            ['0'],
            ['18'],
            ['26'],
            ['34'],
            ['42'],
            ['59'],
            ['67'],
            ['75'],
            ['83'],
            ['91'],
            ['109'],
            ['117'],
            ['125'],
            ['133'],
            ['141'],
            ['158'],
            ['166'],
            ['174'],
            ['182'],
            ['190'],
        ];
    }


    /**
     * Provides a set of invalid Luhn numbers.
     *
     * @return array<array<string>>
     *
     * @psalm-return list{list{'79927398714'}, list{'1234567812345678'}, list{'0a027398714'}, list{'79927a398714'},
     *     list{'a123456781234567'}, list{'123456781234567a'}, list{'25'}, list{'1'}, list{'2'}, list{'3'}, list{'4'},
     *     list{'5'}, list{'6'}, list{'7'}, list{'8'}, list{'9'}, list{'10'}, list{'11'}, list{'12'}, list{'13'},
     *     list{'14'}, list{'15'}, list{'16'}, list{'17'}, list{'19'}, list{'20'}, list{'21'}, list{'191'},
     *     list{'192'}, list{'193'}, list{'194'}, list{'195'}, list{'196'}, list{'197'}, list{'198'}, list{'199'},
     *     list{'241'}, list{'242'}}
     */
    public static function provideInvalidLuhnNumbers(): array
    {
        return [
            ['79927398714'],
            ['1234567812345678'],
            ['0a027398714'],
            ['79927a398714'],
            ['a123456781234567'],
            ['123456781234567a'],
            ['25'],
            ['1'],
            ['2'],
            ['3'],
            ['4'],
            ['5'],
            ['6'],
            ['7'],
            ['8'],
            ['9'],
            ['10'],
            ['11'],
            ['12'],
            ['13'],
            ['14'],
            ['15'],
            ['16'],
            ['17'],
            ['19'],
            ['20'],
            ['21'],
            ['191'],
            ['192'],
            ['193'],
            ['194'],
            ['195'],
            ['196'],
            ['197'],
            ['198'],
            ['199'],
            ['241'],
            ['242'],
        ];
    }


    /**
     * @dataProvider provideValidLuhnNumbers
     */
    public function testValidLuhnNumbers(string $number): void
    {
        $result = $this->getPrivateMethod('isValidLuhnChecksum')->invokeArgs(new SouthAfricanIDValidator(), [$number]);

        self::assertTrue($result);
    }


    /**
     * @dataProvider provideInvalidLuhnNumbers
     */
    public function testInvalidLuhnNumbers(string $number): void
    {
        $result = $this->getPrivateMethod('isValidLuhnChecksum')->invokeArgs(new SouthAfricanIDValidator(), [$number]);
        self::assertFalse($result);
    }


    private function getPrivateMethod(string $methodName): ReflectionMethod
    {
        $reflectionMethod = (new ReflectionClass(SouthAfricanIDValidator::class))->getMethod($methodName);

        /**
         * @noinspection PhpExpressionResultUnusedInspection
         *
         * @psalm-suppress UnusedMethodCall
         */
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }
}
