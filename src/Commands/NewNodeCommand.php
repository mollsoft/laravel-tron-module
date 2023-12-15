<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Models\TronWallet;

class NewNodeCommand extends Command
{
    protected $signature = 'tron:new-node';

    protected $description = 'Create a new Tron Node';

    public function handle(): void
    {
        $this->info('You are about to create a new Tron Node');

        do {
            $error = false;
            $name = $this->ask('Please, enter unique node name');
            if (empty($name)) {
                $error = true;
                $this->error('Node name is required!');
            } else {
                if (TronNode::whereName($name)->count() > 0) {
                    $error = true;
                    $this->error('Name is busy!');
                }
            }
        } while ($error);

        $title = $this->ask('Please, enter node title (optional)');

        do {
            $error = false;
            $apiKey = $this->ask('Please, enter Tron Grid API Key');
            if (empty($apiKey)) {
                $error = true;
                $this->error('API Key is required!');
            } else if( !Str::isUuid($apiKey) ) {
                $error = true;
                $this->error('API Key is not valid!');
            }
        } while ($error);

        $nodeConfig = [
            'url' => 'https://api.trongrid.io',
            'headers' => [
                'TRON-PRO-API-KEY' => $apiKey,
            ],
        ];
        $node = Tron::createNode($name, $title ?: null, $nodeConfig, $nodeConfig);

        $this->info('Tron Node #'.$node->id.' successfully created!');
    }
}
