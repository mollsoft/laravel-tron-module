<?php

namespace Mollsoft\LaravelTronModule\Api\Helpers;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class AmountHelper
{
    public static function toDecimal(string|int|float|BigDecimal $value, int $decimals = 0): BigDecimal
    {
        $value = BigDecimal::of($value);

        return $decimals ? $value->dividedBy(
            BigDecimal::of(10)->power($decimals),
            $decimals,
            RoundingMode::DOWN
        ) : $value;
    }

    public static function sunToDecimal(string|int|float|BigDecimal $value): BigDecimal
    {
        return self::toDecimal($value, 6);
    }

    public static function toSun(string|int|float|BigDecimal $value, int $decimals): int
    {
        $value = BigDecimal::of($value);

        return $value->multipliedBy(
            BigDecimal::of(10)->power($decimals)
        )->toScale(0, RoundingMode::HALF_UP)->toInt();
    }

    public static function decimalToSun(string|int|float|BigDecimal $value): int
    {
        return self::toSun($value, 6);
    }
}
