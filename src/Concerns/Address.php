<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use BIP\BIP44;
use Mollsoft\LaravelTronModule\Api\Helpers\AddressHelper;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Models\TronWallet;
use Mollsoft\LaravelTronModule\Support\Key;

trait Address
{
    public function createAddress(TronWallet $wallet, string $title = null, int $index = null): TronAddress
    {
        if ($index === null) {
            $index = $wallet->addresses()->max('index');
            $index = $index === null ? 0 : ($index + 1);
        }

        $hdKey = BIP44::fromMasterSeed($wallet->seed)
            ->derive("m/44'/195'/0'/0")
            ->deriveChild($index);
        $privateKey = (string)$hdKey->privateKey;

        $address = AddressHelper::toBase58('41'.Key::privateKeyToAddress($privateKey));

        return $wallet->addresses()->create([
            'address' => $address,
            'title' => $title,
            'index' => $index,
            'private_key' => $privateKey,
        ]);
    }

    public function importAddress(TronWallet $wallet, string $address)
    {
        return $wallet->addresses()->create([
            'address' => $address,
            'watch_only' => true,
        ]);
    }

    public function validateAddress(string $address, ?TronNode $node = null): bool
    {
        if( !$node ) {
            $node = Tron::getNode();
        }
        $node->increment('requests', 1);

        return $node->api()->validateAddress($address);
    }
}
