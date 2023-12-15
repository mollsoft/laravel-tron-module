<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Decimal\Decimal;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Models\TronTRC20;

trait TRC20
{
    public function createTRC20(TronNode $node, string $contractAddress): TronTRC20
    {
        $contract = $node->api()->getTRC20Contract($contractAddress);

        /** @var class-string<TronTRC20> $model */
        $model = config('tron.models.trc20');

        return $model::create([
            'address' => $contract->address,
            'name' => $contract->name(),
            'symbol' => $contract->symbol(),
            'decimals' => $contract->decimals(),
        ]);
    }

    public function getTRC20Balance(TronNode $node, TronAddress|string $address, TronTRC20|string $trc20): Decimal
    {
        $contractAddress = $trc20 instanceof TronTRC20 ? $trc20->address : $trc20;
        $contract = $node->api()->getTRC20Contract($contractAddress);
        $address = $address instanceof TronAddress ? $address->address : $address;

        return $contract->balanceOf($address);
    }
}
