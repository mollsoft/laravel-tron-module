<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;

use Brick\Math\BigDecimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Api\Helpers\AddressHelper;
use Mollsoft\LaravelTronModule\Api\Helpers\AmountHelper;

class TRC20TransferDTO
{
    public function __construct(
        public readonly array   $data,
        public readonly string  $txid,
        public readonly Carbon  $time,
        public readonly string  $from,
        public readonly string  $to,
        public readonly string $contractAddress,
        public readonly string $contractName,
        public readonly string $contractSymbol,
        public readonly BigDecimal $value,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'txid' => $this->txid,
            'time' => $this->time->toDateTimeString(),
            'contract' => [
                'address' => $this->contractAddress,
                'name' => $this->contractName,
                'symbol' => $this->contractSymbol,
            ],
            'from' => $this->from,
            'to' => $this->to,
            'value' => $this->value->__toString(),
        ];
    }

    public static function fromArray(array $data): ?static
    {
        if( ($data['type'] ?? null) !== 'Transfer' ) {
            return null;
        }

        return new static(
            data: $data,
            txid: $data['transaction_id'],
            time: Date::createFromTimestampMs($data['block_timestamp']),
            from: AddressHelper::toBase58($data['from']),
            to: AddressHelper::toBase58($data['to']),
            contractAddress: $data['token_info']['address'],
            contractName: $data['token_info']['name'],
            contractSymbol: $data['token_info']['symbol'],
            value: AmountHelper::toDecimal($data['value'], $data['token_info']['decimals'])
        );

    }
}
