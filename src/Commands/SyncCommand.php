<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Mollsoft\LaravelTronModule\Services\TronSync;

class SyncCommand extends Command
{
    protected $signature = 'tron:sync';

    protected $description = 'Start Tron synchronization';

    public function handle(): void
    {
        Cache::lock('tron', 300)->get(function() {
            $this->line('---- Starting sync Tron...');

            try {
                $service = App::make(TronSync::class);

                $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

                $service->run();
            } catch (\Exception $e) {
                $this->error('---- Error: '.$e->getMessage());
            }

            $this->line('---- Completed!');
        });
    }
}
