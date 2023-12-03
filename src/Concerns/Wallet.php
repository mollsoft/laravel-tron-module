<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronWallet;

trait Wallet
{
    /*
     * Create Tron Wallet (without saving in Database)
     */
    public function createWallet(
        string $name,
        string $password,
        string|array $mnemonic,
        string $passphrase = null
    ): TronWallet {
        if (!is_array($mnemonic)) {
            $mnemonic = explode(' ', $mnemonic);
        }

        $seed = Tron::mnemonicSeed($mnemonic, $passphrase);

        /** @var class-string<TronWallet> $walletModel */
        $walletModel = config('tron.models.wallet');

        $wallet = new $walletModel([
            'name' => $name,
            'mnemonic' => implode(" ", $mnemonic),
            'seed' => $seed
        ]);
        $wallet->encrypted()->encrypt($password);
        $wallet->save();

        return $wallet;
    }
}
