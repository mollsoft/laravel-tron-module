<?php

namespace Mollsoft\LaravelTronModule\Api\Helpers;

use Decimal\Decimal;

class AmountHelper
{
    public static function toDecimal(string|int|float|Decimal $value, int $decimals = 0): Decimal
    {
        if( $value instanceof Decimal ) {
            return $value;
        }

        $value = (new Decimal((string)$value));
        if( $decimals ) {
            $value = $value->div(pow(10, $decimals));
        }

        return $value;
    }

    public static function sunToDecimal(string|int|float|Decimal $value): Decimal
    {
        return self::toDecimal($value, 6);
    }

    public static function toSun(string|int|float|Decimal $value, int $decimals): int
    {
        return round(($value instanceof Decimal ? $value->toString() : $value) * pow(10, $decimals));
    }

    public static function decimalToSun(string|int|float|Decimal $value): int
    {
        return self::toSun($value, 6);
    }
}
