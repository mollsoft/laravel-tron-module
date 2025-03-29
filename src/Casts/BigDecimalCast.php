<?php

namespace Mollsoft\LaravelTronModule\Casts;

use Brick\Math\BigDecimal;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class BigDecimalCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): BigDecimal
    {
        return BigDecimal::of((string)($value ?: 0));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value instanceof BigDecimal ? $value->__toString() : $value;
    }
}
