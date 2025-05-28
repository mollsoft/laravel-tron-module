<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Mollsoft\LaravelTronModule\Casts\BigDecimalCast;
use Mollsoft\LaravelTronModule\Casts\EncryptedCast;

class TronWallet extends Model
{
    protected static array $plainPasswords = [];

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
        'trc20_balances',
        'has_password',
        'has_mnemonic',
        'has_seed',
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

        return $this->hasMany($addressModel, 'wallet_id');
    }

    public function transactions(): HasManyThrough
    {
        /** @var class-string<TronTransaction> $transactionModel */
        $transactionModel = config('tron.models.transaction');

        /** @var class-string<TronAddress> $addressModel */
        $addressModel = config('tron.models.address');

        return $this->hasManyThrough(
            $transactionModel,
            $addressModel,
            'wallet_id',
            'address',
            'id',
            'address'
        );
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

    public function unlockWallet(?string $password): void
    {
        self::$plainPasswords[$this->name] = $password;
    }

    public function getPlainPasswordAttribute(): ?string
    {
        return self::$plainPasswords[$this->name] ?? null;
    }

    public function getHasPasswordAttribute(): bool
    {
        return !!$this->password;
    }

    public function getHasMnemonicAttribute(): bool
    {
        return !!$this->mnemonic;
    }

    public function getHasSeedAttribute(): bool
    {
        return !!$this->seed;
    }
}
