<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronWallet;

class ImportAddressCommand extends Command
{
    protected $signature = 'tron:import-address';

    protected $description = 'Import Watch-Only Address for Tron Wallet';

    public function handle(): void
    {
        $this->info('You are about to import watch-only address for Tron Wallet');

        $wallets = TronWallet::get();
        if ($wallets->count() === 0) {
            $this->alert("The list of wallets is empty, first create a wallet.");
            return;
        }

        $walletName = $this->choice('Choice wallet', $wallets->map(fn (TronWallet $wallet) => $wallet->name)->all());

        /** @var TronWallet $wallet */
        $wallet = $wallets->firstWhere('name', $walletName);

        do {
            $error = false;
            $address = $this->ask('Please, enter watch-only address '.$walletName);
            if (!Tron::api()->validateAddress($address, $message)) {
                $this->error($message);
                $error = true;
            }
        } while ($error);

        $address = Tron::importAddress($wallet, $address);
        $address->save();

        $this->info('Watch-Only address '.$address->address.' successfully added!');
    }
}
