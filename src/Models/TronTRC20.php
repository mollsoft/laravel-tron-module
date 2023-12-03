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

    protected ?TRC20Contract $contract = null;

    public function contract(): TRC20Contract
    {
        if ($this->contract === null) {
            $this->contract = Tron::api()->getTRC20Contract($this->address);
        }

        return $this->contract;
    }
}
