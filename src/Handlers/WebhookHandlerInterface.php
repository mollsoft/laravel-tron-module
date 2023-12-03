<?php

namespace Mollsoft\LaravelTronModule\Handlers;

use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronTransaction;

interface WebhookHandlerInterface
{
    public function handle(TronAddress $address, TronTransaction $transaction): void;
}
