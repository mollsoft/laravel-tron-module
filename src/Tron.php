<?php

namespace Mollsoft\LaravelTronModule;

use Illuminate\Database\Eloquent\Model;
use Mollsoft\LaravelTronModule\Api\Api;
use Mollsoft\LaravelTronModule\Concerns\Address;
use Mollsoft\LaravelTronModule\Concerns\Mnemonic;
use Mollsoft\LaravelTronModule\Concerns\Node;
use Mollsoft\LaravelTronModule\Concerns\Transfer;
use Mollsoft\LaravelTronModule\Concerns\TRC20;
use Mollsoft\LaravelTronModule\Concerns\Wallet;
use Mollsoft\LaravelTronModule\Enums\TronModel;
use Mollsoft\LaravelTronModule\Models\TronNode;

class Tron
{
    use Node, Mnemonic, Wallet, Address, TRC20, Transfer;

    /**
     * @param TronModel $model
     * @return class-string<Model>
     */
    public function getModel(TronModel $model): string
    {
        return config('tron.models.'.$model->value);
    }

    /**
     * @return class-string<Api>
     */
    public function getApi(): string
    {
        return config('tron.models.api');
    }

    public function getNode(): TronNode
    {
        /** @var TronNode $node */
        $node = $this->getModel(TronModel::Node)::query()
            ->where('worked', '=', true)
            ->orderBy('requests')
            ->firstOrFail();

        return $node;
    }
}
