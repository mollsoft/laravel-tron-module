<?php

namespace Mollsoft\LaravelTronModule;

use Mollsoft\LaravelTronModule\Api\Api;
use Mollsoft\LaravelTronModule\Concerns\Address;
use Mollsoft\LaravelTronModule\Concerns\Mnemonic;
use Mollsoft\LaravelTronModule\Concerns\TRC20;
use Mollsoft\LaravelTronModule\Concerns\Wallet;

class Tron
{
    use Mnemonic, Wallet, Address, TRC20;

    public function __construct(
        protected readonly Api $api
    ) {
    }

    /*
     * API Object
     */
    public function api(): Api
    {
        return $this->api;
    }
}
