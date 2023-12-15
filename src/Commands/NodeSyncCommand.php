<?php

namespace Mollsoft\LaravelTronModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Mollsoft\LaravelTronModule\Models\TronNode;
use Mollsoft\LaravelTronModule\Services\NodeSync;

class NodeSyncCommand extends Command
{
    protected $signature = 'tron:node-sync {node_id}';

    protected $description = 'Start Tron Node synchronization';

    public function handle(): void
    {
        $this->line('-- Starting sync Tron Node #'.$this->argument('node_id').' ...');

        try {
            /** @var class-string<TronNode> $model */
            $model = config('tron.models.node');
            $node = $model::findOrFail($this->argument('node_id'));

            $this->line('-- Node: *'.$node->name.'*'.$node->title);

            $service = App::make(NodeSync::class, [
                'node' => $node
            ]);

            $service->setLogger(fn(string $message, ?string $type) => $this->{$type ? ($type === 'success' ? 'info' : $type) : 'line'}($message));

            $service->run();
        } catch (\Exception $e) {
            $this->error('-- Error: '.$e->getMessage());
        }

        $this->line('-- Completed!');
    }
}
