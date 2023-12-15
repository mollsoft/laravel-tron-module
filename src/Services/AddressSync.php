<?php

namespace Mollsoft\LaravelTronModule\Services;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Api\Api;
use Mollsoft\LaravelTronModule\Api\DTO\TransferDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferDTO;
use Mollsoft\LaravelTronModule\Enums\TronTransactionType;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Handlers\WebhookHandlerInterface;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Models\TronTransaction;
use Mollsoft\LaravelTronModule\Models\TronTRC20;
use Mollsoft\LaravelTronModule\Models\TronWallet;

class AddressSync extends BaseSync
{
    protected readonly TronWallet $wallet;
    protected readonly TronNode $node;
    protected readonly Api $api;
    protected readonly ?WebhookHandlerInterface $webhookHandler;
    protected readonly array $trc20Addresses;
    /** @var TronTransaction[] $webhooks */
    protected array $webhooks = [];
    protected array $touchConfig = [];

    public function __construct(
        protected readonly TronAddress $address,
        protected readonly bool $force = false
    ) {
        $this->wallet = $this->address->wallet;
        $this->node = $this->wallet->node;
        $this->api = $this->node->api();
        $this->trc20Addresses = TronTRC20::pluck('address')->all();

        $model = config('tron.webhook_handler');
        $this->webhookHandler = $model ? App::make($model) : null;

        $this->touchConfig = config('tron.touch');
    }

    public function run(): void
    {
        parent::run();

        if (
            ($this->touchConfig['enabled'] ?? false)
            &&
            !$this->force
            &&
            $this->address->touch_at
            &&
            $this->address->touch_at < Date::now()->subSeconds($this->touchConfig['waiting_seconds'])
        ) {
            $this->log('No synchronization required, the address has not been touched!', 'success');
            return;
        }

        $this
            ->accountWithResources()
            ->trc20Balances()
            ->transactions()
            ->runWebhooks();
    }

    protected function accountWithResources(): self
    {
        $this->log('Method walletsolidity/getaccount started...');
        $getAccount = $this->api->getAccount($this->address->address);
        $this->log('Method walletsolidity/getaccount finished: '.print_r($getAccount->toArray(), true), 'success');

        $this->log('Method wallet/getaccountresource started...');
        $getAccountResources = $this->api->getAccountResources($this->address->address);
        $this->log(
            'Method wallet/getaccountresource finished: '.print_r($getAccountResources->toArray(), true),
            'success'
        );

        $this->address->update([
            'activated' => $getAccount->activated,
            'balance' => $getAccount->balance,
            'account' => $getAccount->toArray(),
            'account_resources' => $getAccountResources->toArray(),
            'touch_at' => $this->address->touch_at ?: Date::now(),
        ]);

        return $this;
    }

    protected function trc20Balances(): self
    {
        $balances = [];

        foreach ($this->trc20Addresses as $trc20Address) {
            $this->log('Get TRC20 Balance from contract *'.$trc20Address.'* started...');
            $balance = Tron::getTRC20Balance($this->node, $this->address, $trc20Address);
            $this->log(
                'Get TRC20 Balance from contract *'.$trc20Address.'* finished: '.$balance->toString(),
                'success'
            );

            $balances[$trc20Address] = $balance->toString();
        }

        $this->address->update([
            'trc20' => $balances,
        ]);

        return $this;
    }

    protected function transactions(): self
    {
        $minTimestamp = max(($this->address->sync_at?->getTimestamp() ?? 0) - 900, 0) * 1000;

        $this->log('Method v1/accounts/'.$this->address->address.'/transactions started...');
        $transfers = $this->api
            ->getTransfers($this->address->address)
            ->limit(200)
            ->searchInterval(false)
            ->minTimestamp($minTimestamp);
        $this->log('Method v1/accounts/'.$this->address->address.'/transactions finished', 'success');

        $this->log('Method v1/accounts/'.$this->address->address.'/transactions/trc20 started...');
        $trc20Transfers = $this->api
            ->getTRC20Transfers($this->address->address)
            ->limit(200)
            ->minTimestamp($minTimestamp);
        $this->log('Method v1/accounts/'.$this->address->address.'/transactions/trc20 finished', 'success');

        $this->address->update([
            'sync_at' => Date::now(),
            'touch_at' => $this->address->touch_at ?: Date::now(),
        ]);

        foreach ($transfers as $item) {
            $this->handleTransfer($item);
        }

        foreach ($trc20Transfers as $item) {
            $this->handlerTRC20Transfer($item);
        }

        return $this;
    }

    protected function handleTransfer(TransferDTO $transfer): void
    {
        $type = $transfer->to === $this->address->address ?
            TronTransactionType::INCOMING : TronTransactionType::OUTGOING;

        $transaction = TronTransaction::updateOrCreate([
            'txid' => $transfer->txid,
            'address' => $this->address->address,
        ], [
            'type' => $type,
            'time_at' => $transfer->time,
            'from' => $transfer->from,
            'to' => $transfer->to,
            'amount' => $transfer->value,
            'block_number' => $transfer->blockNumber,
            'debug_data' => $transfer->toArray(),
        ]);

        if ($transaction->wasRecentlyCreated) {
            $this->webhooks[] = $transaction;
        }
    }

    protected function handlerTRC20Transfer(TRC20TransferDTO $transfer): void
    {
        if (!in_array($transfer->contractAddress, $this->trc20Addresses)) {
            return;
        }

        $type = $transfer->to === $this->address->address ?
            TronTransactionType::INCOMING : TronTransactionType::OUTGOING;

        $transaction = TronTransaction::updateOrCreate([
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

        if ($transaction->wasRecentlyCreated) {
            $this->webhooks[] = $transaction;
        }

        if( !$transaction->block_number ) {
            try {
                $this->log('We request information about block number of TRC-20 transaction '.$transfer->txid.' ...');
                $blockNumber = $this->api->getTransferBlockNumber($transfer->txid);
                $this->log('Information received successfully: '.$blockNumber, 'success');

                $transaction->update([
                    'block_number' => $blockNumber ?: null
                ]);
            }
            catch(\Exception $e) {
                $this->log('Error: '.$e->getMessage());
            }
        }
    }

    protected function runWebhooks(): self
    {
        if ($this->webhookHandler) {
            foreach ($this->webhooks as $item) {
                $this->log('Call Webhook Handler for Transaction #'.$item->id.': '.print_r($item->toArray(), true));

                $this->webhookHandler->handle($this->address, $item);
            }
        }

        return $this;
    }
}