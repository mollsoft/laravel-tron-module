<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Mollsoft\LaravelTronModule\Enums\TronModel;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronWallet;
use Mollsoft\LaravelTronModule\Services\AddressSync;
use Mollsoft\LaravelTronModule\Services\WalletSync;

class WalletSyncCommand extends Command
{
    protected $signature = 'tron:wallet-sync {wallet_id}';

    protected $description = 'Start Tron Wallet synchronization';

    public function handle(): void
    {
        $this->line('- Starting sync Tron Wallet #'.$this->argument('wallet_id').' ...');

        try {
            /** @var class-string<TronWallet> $model */
            $model = Tron::getModel(TronModel::Wallet);
            $wallet = $model::findOrFail($this->argument('wallet_id'));

            $this->line('- Wallet: *'.$wallet->name.'*'.$wallet->title);

            $service = App::make(WalletSync::class, [
                'wallet' => $wallet
            ]);

            $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

            $service->run();
        } catch (\Exception $e) {
            $this->error('- Error: '.$e->getMessage());
        }

        $this->line('- Completed!');
    }
}
