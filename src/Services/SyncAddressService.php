<?php

namespace Mollsoft\LaravelTronModule\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Mollsoft\LaravelTronModule\Api\DTO\TransferDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferDTO;
use Mollsoft\LaravelTronModule\Enums\TronTransactionType;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Handlers\WebhookHandlerInterface;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronTransaction;
use Mollsoft\LaravelTronModule\Models\TronTRC20;

class SyncAddressService
{
    protected TronAddress $address;
    protected readonly array $trc20Addresses;

    public function __construct()
    {
        $this->trc20Addresses = TronTRC20::pluck('address')->all();
    }

    public function run(TronAddress $address): void
    {
        $this->address = $address;

        $this
            ->accountWithResources()
            ->trc20Balances()
            ->transactions();
    }

    protected function accountWithResources(): self
    {
        $getAccount = Tron::api()->getAccount($this->address->address);
        $getAccountResources = Tron::api()->getAccountResources($this->address->address);

        $this->address->update([
            'activated' => $getAccount->activated,
            'balance' => $getAccount->balance,
            'account' => $getAccount->toArray(),
            'account_resources' => $getAccountResources->toArray(),
        ]);

        return $this;
    }

    protected function trc20Balances(): self
    {
        $this->address->trc20 = TronTRC20::get()->mapWithKeys(function (TronTRC20 $trc20) {
            return [
                $trc20->address => $trc20->contract()->balanceOf($this->address->address)->toString(),
            ];
        })->all();
        $this->address->save();

        return $this;
    }

    protected function transactions(): self
    {
        $minTimestamp = max(($this->address->sync_at?->getTimestamp() ?? 0) - 900, 0) * 1000;

        $transfers = Tron::api()
            ->getTransfers($this->address->address)
            ->limit(200)
            ->searchInterval(false)
            ->minTimestamp($minTimestamp);

        $trc20Transfers = Tron::api()
            ->getTRC20Transfers($this->address->address)
            ->limit(200)
            ->minTimestamp($minTimestamp);

        $this->address->update([
            'sync_at' => Date::now(),
        ]);

        $transactions = [];

        foreach ($transfers as $transfer) {
            $transaction = $this->handleTransfer($transfer);
            if ($transaction) {
                $transactions[] = $transaction;
            }
        }

        foreach ($trc20Transfers as $trc20Transfer) {
            $transaction = $this->handlerTRC20Transfer($trc20Transfer);
            if ($transaction) {
                $transactions[] = $transaction;
            }
        }

        foreach ($transactions as $transaction) {
            try {
                $this->webhook($transaction);
            } catch (\Exception $e) {
                Log::error('Tron Transaction '.$transaction->txid.' webhook error: '.$e->getMessage());
            }
        }

        return $this;
    }

    protected function handleTransfer(TransferDTO $transfer): TronTransaction
    {
        $type = $transfer->to === $this->address->address ?
            TronTransactionType::INCOMING : TronTransactionType::OUTGOING;

        return TronTransaction::updateOrCreate([
            'txid' => $transfer->txid,
            'address' => $this->address->address,
        ], [
            'type' => $type,
            'time_at' => $transfer->time,
            'from' => $transfer->from,
            'to' => $transfer->to,
            'amount' => $transfer->value,
            'debug_data' => $transfer->toArray(),
        ]);
    }

    protected function handlerTRC20Transfer(TRC20TransferDTO $transfer): ?TronTransaction
    {
        if (!in_array($transfer->contractAddress, $this->trc20Addresses)) {
            return null;
        }

        $type = $transfer->to === $this->address->address ?
            TronTransactionType::INCOMING : TronTransactionType::OUTGOING;

        return TronTransaction::updateOrCreate([
            'txid' => $transfer->txid,
            'address' => $this->address->address,
        ], [
            'type' => $type,
            'time_at' => $transfer->time,
            'from' => $transfer->from,
            'to' => $transfer->to,
            'amount' => $transfer->value,
            'trc20_contract_address' => $transfer->contractAddress,
            'debug_data' => $transfer->toArray(),
        ]);
    }

    protected function webhook(TronTransaction $transaction): void
    {
        if ($transaction->wasRecentlyCreated) {
            /** @var class-string<WebhookHandlerInterface> $webhookHandlerModel */
            $webhookHandlerModel = config('tron.webhook_handler');
            if ($webhookHandlerModel) {
                /** @var WebhookHandlerInterface $webhookHandler */
                $webhookHandler = App::make($webhookHandlerModel);
                App::call([$webhookHandler, 'handle'], [
                    'address' => $this->address,
                    'transaction' => $transaction
                ]);
            }
        }
    }
}
