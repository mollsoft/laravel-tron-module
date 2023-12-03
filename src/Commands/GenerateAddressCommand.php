<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronWallet;

class GenerateAddressCommand extends Command
{
    protected $signature = 'tron:generate-address';

    protected $description = 'Generate Address for Tron Wallet';

    public function handle(): void
    {
        $this->info('You are about to generate address for Tron Wallet');

        $wallets = TronWallet::get();
        if ($wallets->count() === 0) {
            $this->alert("The list of wallets is empty, first create a wallet.");
            return;
        }

        $walletName = $this->choice('Choice wallet', $wallets->map(fn(TronWallet $wallet) => $wallet->name)->all());

        /** @var TronWallet $wallet */
        $wallet = $wallets->firstWhere('name', $walletName);

        do {
            $error = false;
            $password = $this->ask('Please, enter password for wallet '.$walletName);
            if (!$wallet->encrypted()->unlock($password)) {
                $this->error('Password is wrong!');
                $error = true;
            }
        } while ($error);

        $address = Tron::createAddress($wallet);
        $address->save();

        $this->info('Address '.$address->address.' with index '.$address->index.' successfully generated!');
    }
}
