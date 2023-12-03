<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Casts\DecimalCast;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Wallet\Encrypted;

class TronWallet extends Model
{
    protected $fillable = [
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
        'plain_password',
        'plain_mnemonic',
        'plain_seed',
        'trc20_balances'
    ];

    protected $casts = [
        'sync_at' => 'datetime',
        'balance' => DecimalCast::class,
        'trc20' => 'json',
    ];

    protected ?Encrypted $encrypted = null;

    public function encrypted(): Encrypted
    {
        if ($this->encrypted === null) {
            $this->encrypted = new Encrypted($this);
        }

        return $this->encrypted;
    }

    public function addresses(): HasMany
    {
        /** @var class-string<TronAddress> $addressModel */
        $addressModel = config('tron.models.address');

        return $this->hasMany($addressModel, 'wallet_id', 'id');
    }

    protected function plainPassword(): Attribute
    {
        return new Attribute(get: fn () => $this->encrypted()->password());
    }

    protected function plainMnemonic(): Attribute
    {
        return new Attribute(get: fn () => $this->encrypted()->mnemonic());
    }

    protected function plainSeed(): Attribute
    {
        return new Attribute(get: fn () => $this->encrypted()->seed());
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
}
