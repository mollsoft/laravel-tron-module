<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Mollsoft\LaravelTronModule\Enums\TronModel;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronNode;

trait Node
{
    public function createNode(string $name, ?string $title, array $fullNode, array $solidityNode): TronNode
    {
        if (!($fullNode['url'] ?? null)) {
            throw new \Exception('URL param not found in Full Node');
        }
        if (!($solidityNode['url'] ?? null)) {
            throw new \Exception('URL param not found in Solidity Node');
        }

        /** @var class-string<TronNode> $nodeModel */
        $nodeModel = Tron::getModel(TronModel::Node);
        $node = new $nodeModel([
            'name' => $name,
            'title' => $title,
            'full_node' => $fullNode,
            'solidity_node' => $solidityNode,
            'requests' => 1,
            'worked' => true,
        ]);

        $getBlock = $node->api()->manager->request('wallet/getblock');
        $blockNumber = $getBlock['block_header']['raw_data']['number'] ?? null;
        if (is_null($blockNumber)) {
            throw new \Exception('Current block is unknown!');
        }
        $node->block_number = $blockNumber;

        $node->save();

        return $node;
    }

    public function createTronGridNode(string $apiKey, string $name, ?string $title = null, ?string $proxy = null): TronNode
    {
        /** @var class-string<TronNode> $nodeModel */
        $nodeModel = Tron::getModel(TronModel::Node);

        $isUniqueApiKey = $nodeModel::query()->where('full_node', 'like', '%' . $apiKey . '%')->count() === 0;
        if (!$isUniqueApiKey) {
            throw new \Exception('API Key already exists.');
        }

        $node = new $nodeModel([
            'name' => $name,
            'title' => $title,
            'full_node' => [
                'url' => 'https://api.trongrid.io',
                'headers' => [
                    'TRON-PRO-API-KEY' => $apiKey,
                ],
                'proxy' => $proxy,
            ],
            'solidity_node' => [
                'url' => 'https://api.trongrid.io',
                'headers' => [
                    'TRON-PRO-API-KEY' => $apiKey,
                ],
                'proxy' => $proxy,
            ],
            'requests' => 1,
            'worked' => true,
        ]);

        $getBlock = $node->api()->manager->request('wallet/getblock');
        $blockNumber = $getBlock['block_header']['raw_data']['number'] ?? null;
        if (is_null($blockNumber)) {
            throw new \Exception('Current block is unknown!');
        }
        $node->block_number = $blockNumber;

        $node->save();

        return $node;
    }
}