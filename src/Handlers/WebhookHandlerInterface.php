<?php

namespace Mollsoft\LaravelTronModule\Handlers;

use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronDeposit;
use Mollsoft\LaravelTronModule\Models\TronTransaction;

interface WebhookHandlerInterface
{
    public function handle(TronDeposit $deposit): void;
}
