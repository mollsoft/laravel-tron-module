<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronNode;

class NewTRC20Command extends Command
{
    protected $signature = 'tron:new-trc20';

    protected $description = 'Create TRC-20 for Tron';

    public function handle(): void
    {
        $this->info('You are about to create a new Tron TRC-20');

        if (TronNode::count() === 0) {
            $this->alert("The list of nodes is empty, first create a node.");
            return;
        }

        do {
            $error = false;
            $contractAddress = $this->ask('Contract Address');

            if (!Tron::validateAddress($contractAddress)) {
                $error = true;
                $this->error('Address is not valid.');
            }
        } while ($error);

        $trc20 = Tron::createTRC20($contractAddress);
        $trc20->save();

        $this->info('TRC-20 '.$trc20->name.' ('.$trc20->symbol.') successfully created!');
    }
}
