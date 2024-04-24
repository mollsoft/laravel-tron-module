<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Mollsoft\LaravelTronModule\Enums\TronModel;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Models\TronWallet;

trait Wallet
{
    public function createWallet(
        string $name,
        string|array|int|null $mnemonic = null,
        ?string $passphrase = null,
        ?TronNode $node = null,
    ): TronWallet {
        if (is_string($mnemonic)) {
            $mnemonic = explode(' ', $mnemonic);
        } elseif (is_null($mnemonic) || is_int($mnemonic)) {
            $mnemonic = Tron::mnemonicGenerate($mnemonic);
        }

        $seed = Tron::mnemonicSeed($mnemonic, $passphrase);

        /** @var class-string<TronWallet> $walletModel */
        $walletModel = Tron::getModel(TronModel::Wallet);

        $wallet = $walletModel::create([
            'node_id' => $node?->id,
            'name' => $name,
            'mnemonic' => implode(" ", $mnemonic),
            'seed' => $seed,
        ]);

        Tron::createAddress($wallet, 'Primary Address', 0);

        return $wallet;
    }
}
