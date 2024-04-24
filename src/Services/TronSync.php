<?php

namespace Mollsoft\LaravelTronModule\Services;

use Illuminate\Support\Facades\App;
use Mollsoft\LaravelTronModule\Enums\TronModel;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Models\TronWallet;

class TronSync extends BaseSync
{
    public function run(): void
    {
        parent::run();

        $this
            ->syncNodes()
            ->syncWallets();
    }

    protected function syncNodes(): self
    {
        /** @var class-string<TronNode> $model */
        $model = Tron::getModel(TronModel::Node);

        $model::query()
            ->orderBy('sync_at')
            ->orderBy('name')
            ->each(function (TronNode $node) {
                $this->log('--- Staring sync Node ' . $node->name . '...');

                $service = App::make(NodeSync::class, [
                    'node' => $node
                ]);

                $service->setLogger($this->logger);

                $service->run();

                $this->log('--- Finished sync Node ' . $node->name, 'success');
            });

        return $this;
    }

    protected function syncWallets(): self
    {
        /** @var class-string<TronWallet> $model */
        $model = Tron::getModel(TronModel::Wallet);

        $model::query()
            ->orderBy('sync_at')
            ->get()
            ->each(function (TronWallet $wallet) {
                $this->log('--- Staring sync Wallet ' . $wallet->name . '...');

                $service = App::make(WalletSync::class, [
                    'wallet' => $wallet
                ]);

                $service->setLogger($this->logger);

                $service->run();

                $this->log('--- Finished sync Wallet ' . $wallet->name, 'success');
            });

        return $this;
    }
}