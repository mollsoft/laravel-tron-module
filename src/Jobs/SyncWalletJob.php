<?php

namespace Mollsoft\LaravelTronModule\Jobs;

use Decimal\Decimal;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Mollsoft\LaravelTronModule\Models\TronWallet;
use Mollsoft\LaravelTronModule\Services\SyncAddressService;

class SyncWalletJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(protected readonly TronWallet $wallet)
    {
    }

    public function handle(SyncAddressService $service): void
    {
        foreach ($this->wallet->addresses as $address) {
            $service->run($address);
        }

        $balance = new Decimal(0);
        $trc20 = [];

        foreach ($this->wallet->addresses()->get() as $address) {
            $balance = $balance->add((string)($address->balance ?: 0));
            foreach ($address->trc20 as $k => $v) {
                $current = new Decimal($trc20[$k] ?? 0);
                $trc20[$k] = $current->add($v)->toString();
            }
        }

        $this->wallet->update([
            'sync_at' => Date::now(),
            'balance' => $balance,
            'trc20' => $trc20,
        ]);
    }
}
