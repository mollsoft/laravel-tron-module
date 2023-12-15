<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Services\AddressSync;

class AddressSyncCommand extends Command
{
    protected $signature = 'tron:address-sync {address_id}';

    protected $description = 'Start Tron Address synchronization';

    public function handle(): void
    {
        $this->line('Starting sync Tron Address #'.$this->argument('address_id').' ...');

        try {
            /** @var class-string<TronAddress> $model */
            $model = config('tron.models.address');
            $address = $model::findOrFail($this->argument('address_id'));

            $this->line('Address: *'.$address->address.'* '.$address->title);
            $this->line('Wallet: *'.$address->wallet->name.'*'.$address->wallet->title);

            $service = App::make(AddressSync::class, [
                'address' => $address,
                'force' => true,
            ]);

            $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

            $service->run();
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }

        $this->line('Completed!');
    }
}
