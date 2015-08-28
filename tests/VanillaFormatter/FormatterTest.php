<?php

namespace Rentalhost\VanillaFormatter;

use PHPUnit_Framework_TestCase;

/**
 * Class PhoneFormatterTest
 * @package Rentalhost\VanillaFormatter
 */
class PhoneFormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test normalize static method.
     *
     * @param  string $expectedValue Expected value.
     * @param  string $value         Value to normalize.
     * @param  string $type          Normalization type.
     *
     * @covers       Rentalhost\VanillaFormatter\Formatter::normalize
     * @dataProvider dataNormalize
     */
    public function testNormalize($expectedValue, $value, $type = null)
    {
        static::assertSame($expectedValue, Formatter::normalize($value, $type));
    }

    /**
     * @return array
     */
    public function dataNormalize()
    {
        return [
            [ '', '' ],
            [ '1122223333', '11 2222 3333' ],
            [ '551122223333', '+55 11 2222 3333' ],
            [ '+551122223333', '+55 11 2222 3333', 'phone' ],
            [ '+551122223333', '+55 11 2222 3333', 'PHONE' ],
            [ '551122223333', '+55 11 2222 3333', 'unknow' ],
        ];
    }

    /**
     * Test format static method.
     *
     * @param  string $expectedValue Expected value.
     * @param  string $value         Value to normalize.
     * @param  string $type          Value type.
     * @param  string $region        Value region.
     *
     * @covers       Rentalhost\VanillaFormatter\Formatter::format
     * @covers       Rentalhost\VanillaFormatter\Formatter::getFormats
     * @covers       Rentalhost\VanillaFormatter\Formatter::getCompatibleFormat
     * @dataProvider dataFormat
     */
    public function testFormat($expectedValue, $value, $type, $region = null)
    {
        static::assertEquals($expectedValue, Formatter::format($value, $type, $region));
    }

    /**
     * @return array
     */
    public function dataFormat()
    {
        return [
            [ '', '', null ],
            // General: credit-card type.
            1000000 =>
                [ '0000 1111 2222 3333', '0000111122223333', 'credit-card' ],
            [ '0000 111111 22222', '000011111122222', 'credit-card' ],
            [ '0000 111111 2222', '00001111112222', 'credit-card' ],
            [ '0000 111 222 333', '0000111222333', 'credit-card' ],
            // Brazil.
            1000100 =>
                [ '111.222.333-00', '11122233300', 'cpf', 'brazil' ],
            [ '11.222.333/4444-00', '11222333444400', 'cnpj', 'brazil' ],
            [ '11111-000', '11111000', 'cep', 'brazil' ],
            [ '1111-2222', '11112222', 'phone', 'brazil' ],
            [ '91111-2222', '911112222', 'phone', 'brazil' ],
            [ '(00) 1111-2222', '0011112222', 'phone', 'brazil' ],
            [ '(00) 91111-2222', '00911112222', 'phone', 'brazil' ],
            [ '0800 111 222', '0800111222', 'phone', 'brazil' ],
            [ '0800 111 2222', '08001112222', 'phone', 'brazil' ],
            // General: invalid types.
            9000000 =>
                [ '00001111222233335555', '00001111222233335555', 'credit-card' ],
            // Brazil: invalid types.
            9000100 =>
                [ '811112222', '811112222', 'phone', 'brazil' ],
            [ '00811112222', '00811112222', 'phone', 'brazil' ],
            [ '08011112222', '08011112222', 'phone', 'brazil' ],
            // Coverage:
            9900000 =>
                [ '0000111122223333', '0000111122223333', 'credit-card', 'coverage' ],
            [ '11112222', '11112222', 'phone', null ],
            [ '11112222', '11112222', 'phone', 'coverage' ],
        ];
    }
}
