<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Models\TronWallet;

trait Wallet
{
    public function createWallet(
        TronNode $node,
        string $name,
        string|array|int|null $mnemonic = null,
        string $passphrase = null
    ): TronWallet {
        if (is_string($mnemonic)) {
            $mnemonic = explode(' ', $mnemonic);
        } elseif (is_null($mnemonic) || is_int($mnemonic)) {
            $mnemonic = Tron::mnemonicGenerate($mnemonic);
        }

        $seed = Tron::mnemonicSeed($mnemonic, $passphrase);

        $wallet = $node->wallets()
            ->create([
                'name' => $name,
                'mnemonic' => implode(" ", $mnemonic),
                'seed' => $seed
            ]);


        Tron::createAddress($wallet, 'Primary Address', 0);

        return $wallet;
    }
}
