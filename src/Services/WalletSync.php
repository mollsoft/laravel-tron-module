<?php

namespace Mollsoft\LaravelTronModule\Services;

use Brick\Math\BigDecimal;
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
        $balance = BigDecimal::of('0');
        $trc20 = [];

        foreach ($this->wallet->addresses as $address) {
            $balance = $balance->plus(($address->balance ?: 0));
            foreach ($address->trc20 as $k => $v) {
                $current = BigDecimal::of($trc20[$k] ?? 0);
                $trc20[$k] = $current->plus($v)->__toString();
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