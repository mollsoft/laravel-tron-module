<?php

namespace Mollsoft\LaravelTronModule\Facades;

use Illuminate\Support\Facades\Facade;

class Tron extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Mollsoft\LaravelTronModule\Tron::class;
    }
}
