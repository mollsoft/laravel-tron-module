<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mollsoft\LaravelTronModule\Casts\DecimalCast;

class TronWallet extends Model
{
    protected $fillable = [
        'node_id',
        'name',
        'title',
        'password',
        'mnemonic',
        'seed',
        'sync_at',
        'balance',
        'trc20'
    ];

    protected $hidden = [
        'password',
        'mnemonic',
        'seed',
        'trc20',
    ];

    protected $appends = [
        'trc20_balances'
    ];

    protected $casts = [
        'password' => 'encrypted',
        'mnemonic' => 'encrypted',
        'seed' => 'encrypted',
        'sync_at' => 'datetime',
        'balance' => DecimalCast::class,
        'trc20' => 'json',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(TronNode::class, 'node_id');
    }

    public function addresses(): HasMany
    {
        /** @var class-string<TronAddress> $addressModel */
        $addressModel = config('tron.models.address');

        return $this->hasMany($addressModel, 'wallet_id');
    }

    protected function trc20Balances(): Attribute
    {
        return new Attribute(
            get: fn () => TronTRC20::get()->map(fn (TronTRC20 $trc20) => [
                ...$trc20->only(['address', 'name', 'symbol', 'decimals']),
                'balance' => $this->trc20[$trc20->address] ?? null,
            ])
        );
    }

    public function deposits(): HasMany
    {
        /** @var class-string<TronDeposit> $model */
        $model = config('tron.models.deposit');

        return $this->hasMany($model, 'wallet_id');
    }
}
