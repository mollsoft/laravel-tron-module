<?php

namespace Mollsoft\LaravelTronModule;

use Mollsoft\LaravelTronModule\Concerns\Address;
use Mollsoft\LaravelTronModule\Concerns\Mnemonic;
use Mollsoft\LaravelTronModule\Concerns\Node;
use Mollsoft\LaravelTronModule\Concerns\Transfer;
use Mollsoft\LaravelTronModule\Concerns\TRC20;
use Mollsoft\LaravelTronModule\Concerns\Wallet;

class Tron
{
    use Node, Mnemonic, Wallet, Address, TRC20, Transfer;
}
