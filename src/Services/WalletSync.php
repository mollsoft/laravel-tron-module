<?php

namespace Mollsoft\LaravelTronModule\Services;

use Closure;
use Decimal\Decimal;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Models\TronWallet;

class WalletSync extends BaseSync
{
    public function __construct(
        protected readonly TronWallet $wallet,
    ) {
    }

    public function run(): void
    {
        parent::run();

        $this
            ->syncAddresses()
            ->calculateBalances();
    }

    protected function syncAddresses(): self
    {
        foreach ($this->wallet->addresses as $address) {
            $this->log('- Started sync address '.$address->address.'...');

            $service = App::make(AddressSync::class, [
                'address' => $address
            ]);

            $service->setLogger($this->logger);

            $service->run();

            $this->log('- Finished sync address '.$address->address, 'success');
        }

        return $this;
    }

    protected function calculateBalances(): self
    {
        $balance = new Decimal('0');
        $trc20 = [];

        foreach ($this->wallet->addresses as $address) {
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

        return $this;
    }
}