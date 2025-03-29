<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Mollsoft\LaravelTronModule\Casts\BigDecimalCast;
use Mollsoft\LaravelTronModule\Enums\TronTransactionType;

class TronTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'txid',
        'address',
        'type',
        'time_at',
        'from',
        'to',
        'amount',
        'trc20_contract_address',
        'block_number',
        'debug_data',
    ];

    protected $appends = [
        'symbol'
    ];

    protected $casts = [
        'type' => TronTransactionType::class,
        'time_at' => 'datetime',
        'amount' => BigDecimalCast::class,
        'block_number' => 'integer',
        'debug_data' => 'json',
    ];

    public function addresses(): HasMany
    {
        /** @var class-string<TronAddress> $addressModel */
        $addressModel = config('tron.models.address');

        return $this->hasMany($addressModel, 'address', 'address');
    }

    public function wallets(): HasManyThrough
    {
        /** @var class-string<TronWallet> $walletModel */
        $walletModel = config('tron.models.wallet');

        /** @var class-string<TronAddress> $addressModel */
        $addressModel = config('tron.models.address');

        return $this->hasManyThrough(
            $walletModel,
            $addressModel,
            'address',
            'id',
            'address',
            'wallet_id'
        );
    }

    public function trc20(): BelongsTo
    {
        return $this->belongsTo(TronTRC20::class, 'trc20_contract_address', 'address');
    }

    protected function symbol(): Attribute
    {
        return new Attribute(
            get: fn () => $this->trc20_contract_address ? ($this->trc20?->symbol ?: 'TOKEN') : 'TRX'
        );
    }
}
