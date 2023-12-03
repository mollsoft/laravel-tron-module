<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronTRC20;

trait TRC20
{
    /*
     * Create TRC-20 Token (widthout save in Database)
     */
    public function createTRC20(string $contractAddress): TronTRC20
    {
        $contract = Tron::api()->getTRC20Contract($contractAddress);

        /** @var class-string<TronTRC20> $addressModel */
        $trc20Model = config('tron.models.trc20');

        return new $trc20Model([
            'address' => $contract->address,
            'name' => $contract->name(),
            'symbol' => $contract->symbol(),
            'decimals' => $contract->decimals(),
        ]);
    }
}
