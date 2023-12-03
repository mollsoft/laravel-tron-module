<?php

namespace Mollsoft\LaravelTronModule\Handlers;

use Illuminate\Support\Facades\Log;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronTransaction;

class EmptyWebhookHandler implements WebhookHandlerInterface
{
    public function handle(TronAddress $address, TronTransaction $transaction): void
    {
        Log::error('NEW TRANSACTION FOR ADDRESS '.$address->id.' = '.$transaction->txid);
    }
}
