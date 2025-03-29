<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mollsoft\LaravelTronModule\Casts\BigDecimalCast;
use Mollsoft\LaravelTronModule\Casts\EncryptedCast;

class TronWallet extends Model
{
    public ?string $plain_password = null;

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
        'mnemonic' => EncryptedCast::class,
        'seed' => EncryptedCast::class,
        'sync_at' => 'datetime',
        'balance' => BigDecimalCast::class,
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

        return $this->hasMany($addressModel, 'wallet_id')->withParentWallet($this);
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
