<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mollsoft\LaravelTronModule\Api\Api;
use Mollsoft\LaravelTronModule\Api\HttpProvider;

class TronNode extends Model
{
    public $timestamps = false;

    protected ?Api $_api = null;

    protected $fillable = [
        'name',
        'title',
        'full_node',
        'solidity_node',
        'block_number',
        'requests',
        'requests_at',
        'sync_at',
        'worked',
    ];

    protected $casts = [
        'full_node' => 'json',
        'solidity_node' => 'json',
        'block_number' => 'integer',
        'requests' => 'integer',
        'requests_at' => 'date',
        'sync_at' => 'datetime',
        'worked' => 'boolean',
    ];

    public function wallets(): HasMany
    {
        /** @var class-string<TronWallet> $model */
        $model = config('tron.models.wallet');

        return $this->hasMany($model, 'node_id');
    }

    public function api(): Api
    {
        if( is_null( $this->_api ) ) {
            $fullNode = new HttpProvider(
                baseUri: $this->full_node['url'],
                headers: $this->full_node['headers'] ?? [],
                user: $this->full_node['username'] ?? null,
                password: $this->full_node['password'] ?? null,
                proxy: $this->full_node['proxy'] ?? null,
            );

            $solidityNode = new HttpProvider(
                baseUri: $this->solidity_node['url'],
                headers: $this->solidity_node['headers'] ?? [],
                user: $this->solidity_node['username'] ?? null,
                password: $this->solidity_node['password'] ?? null,
                proxy: $this->solidity_node['proxy'] ?? null,
            );

            /** @var class-string<Api> $model */
            $model = config('tron.models.api');

            $this->_api = new $model(
                fullNode: $fullNode,
                solidityNode: $solidityNode
            );
        }

        return $this->_api;
    }
}
