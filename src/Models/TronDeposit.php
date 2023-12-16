<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mollsoft\LaravelTronModule\Casts\DecimalCast;

class TronDeposit extends Model
{
    protected $fillable = [
        'wallet_id',
        'address_id',
        'trc20_id',
        'txid',
        'amount',
        'block_height',
        'confirmations',
        'time_at',
    ];

    protected $casts = [
        'amount' => DecimalCast::class,
        'block_height' => 'integer',
        'confirmations' => 'integer',
        'time_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        /** @var class-string<TronWallet> $model */
        $model = config('tron.models.wallet');

        return $this->belongsTo($model, 'wallet_id');
    }

    public function address(): BelongsTo
    {
        /** @var class-string<TronAddress> $model */
        $model = config('tron.models.address');

        return $this->belongsTo($model, 'address_id');
    }

    public function trc20(): BelongsTo
    {
        /** @var class-string<TronTRC20> $model */
        $model = config('tron.models.trc20');

        return $this->belongsTo($model, 'trc20_id');
    }
}
