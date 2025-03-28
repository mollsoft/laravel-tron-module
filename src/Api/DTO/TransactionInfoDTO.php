<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;

use Brick\Math\BigDecimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Api\Helpers\AmountHelper;

class TransactionInfoDTO
{
    public function __construct(
        public readonly array $data,
        public readonly string $txid,
        public readonly int $blockNumber,
        public readonly Carbon $blockTime,
        public readonly ?bool $success,
        public readonly ?int $bandwidth,
        public readonly ?int $energy,
        public readonly ?BigDecimal $fee,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'txid' => $this->txid,
            'blockNumber' => $this->blockNumber,
            'blockTime' => $this->blockTime->toDateTimeString(),
            'bandwidth' => $this->bandwidth,
            'energy' => $this->energy,
            'fee' => $this->fee?->__toString(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            data: $data,
            txid: $data['id'],
            blockNumber: (int)$data['blockNumber'],
            blockTime: Date::createFromTimestampMs($data['blockTimeStamp']),
            success: isset( $data['receipt']['result'] ) ? $data['receipt']['result'] === 'SUCCESS' : null,
            bandwidth: $data['receipt']['net_usage'] ?? null,
            energy: $data['receipt']['energy_usage_total'] ?? null,
            fee: isset($data['fee']) ? AmountHelper::sunToDecimal($data['fee']) : null,
        );
    }
}
