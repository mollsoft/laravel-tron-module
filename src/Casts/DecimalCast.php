<?php

namespace Mollsoft\LaravelTronModule\Casts;

use Decimal\Decimal;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class DecimalCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Decimal
    {
        return new Decimal((string)($value ?: 0));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value instanceof Decimal ? $value->toString() : $value;
    }
}
