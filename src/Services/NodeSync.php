<?php

namespace Mollsoft\LaravelTronModule\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Models\TronNode;

class NodeSync extends BaseSync
{
    public function __construct(
        protected readonly TronNode $node,
    ) {
    }

    public function run(): void
    {
        parent::run();

        $this->syncBlock();
    }

    protected function syncBlock(): self
    {
        try {
            $getBlock = $this->node->api()->manager->request('wallet/getblock');
            $blockNumber = $getBlock['block_header']['raw_data']['number'] ?? null;
            if( is_null($blockNumber) ) {
                throw new \Exception('Current block is unknown!');
            }
        }
        catch(\Exception $e) {
            $this->node->update([
                'worked' => false,
            ]);

            throw $e;
        }

        $this->node->increment('requests');
        $this->node->update([
            'block_number' => $blockNumber,
            'sync_at' => Date::now(),
            'worked' => true,
        ]);

        return $this;
    }

    protected function syncWallets(): self
    {
//        foreach( $this->node->wallets as $wallet ) {
//            $this->log('-- Started sync wallet '.$wallet->name.'...');
//
//            $service = App::make(WalletSync::class, [
//                'wallet' => $wallet,
//            ]);
//
//            $service->setLogger($this->logger);
//
//            $service->run();
//
//            $this->log('-- Finished sync wallet '.$wallet->name, 'success');
//        }

        return $this;
    }
}