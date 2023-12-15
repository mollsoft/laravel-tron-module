<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Mollsoft\LaravelTronModule\Models\TronNode;

trait Node
{
    public function createNode(string $name, ?string $title, array $fullNode, array $solidityNode): TronNode
    {
        if( !($fullNode['url'] ?? null) ) {
            throw new \Exception('URL param not found in Full Node');
        }
        if( !($solidityNode['url'] ?? null) ) {
            throw new \Exception('URL param not found in Solidity Node');
        }

        /** @var class-string<TronNode> $model */
        $model = config('tron.models.node');
        $node = new $model([
            'name' => $name,
            'title' => $title,
            'full_node' => $fullNode,
            'solidity_node' => $solidityNode,
        ]);

        $getBlock = $node->api()->manager->request('wallet/getblock');
        $blockNumber = $getBlock['block_header']['raw_data']['number'] ?? null;
        if( is_null($blockNumber) ) {
            throw new \Exception('Current block is unknown!');
        }
        $node->block_number = $blockNumber;
        
        $node->save();

        return $node;
    }
}