<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;


use Decimal\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Api\Helpers\AmountHelper;

class AccountDTO
{
    public function __construct(
        public readonly string   $address,
        public readonly array    $data,
        public readonly bool     $activated,
        public readonly ?Decimal $balance,
        public readonly ?Carbon  $createTime,
        public readonly ?Carbon  $lastOperationTime,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'activated' => $this->activated,
            'balance' => $this->balance?->toString(),
            'createTime' => $this->createTime?->toDateTimeString(),
            'lastOperationTime' => $this->lastOperationTime?->toDateTimeString(),
        ];
    }

    public static function fromArray(string $address, array $data): static
    {
        $activated = isset($data['create_time']);
        $balance = $activated ? AmountHelper::sunToDecimal($data['balance'] ?? 0) : null;
        $createTime = $activated ? Date::createFromTimestampMs($data['create_time']) : null;
        $lastOperationTime = $activated && isset($data['latest_opration_time']) ? Date::createFromTimestampMs($data['latest_opration_time']) : null;

        return new static(
            address: $address,
            data: $data,
            activated: $activated,
            balance: $balance,
            createTime: $createTime,
            lastOperationTime: $lastOperationTime,
        );
    }
}
