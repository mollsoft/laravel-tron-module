<?php

namespace Mollsoft\LaravelTronModule\Models;

use Illuminate\Database\Eloquent\Model;
use Mollsoft\LaravelTronModule\Api\TRC20Contract;
use Mollsoft\LaravelTronModule\Facades\Tron;

class TronTRC20 extends Model
{
    public $timestamps = false;

    protected $table = 'tron_trc20';

    protected $fillable = [
        'address',
        'name',
        'symbol',
        'decimals',
    ];

    protected $casts = [
        'decimals' => 'integer',
    ];
}
