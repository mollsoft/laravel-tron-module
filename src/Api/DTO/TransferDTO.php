<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;

use Decimal\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Mollsoft\LaravelTronModule\Api\Helpers\AddressHelper;
use Mollsoft\LaravelTronModule\Api\Helpers\AmountHelper;

class TransferDTO
{
    public function __construct(
        public readonly array   $data,
        public readonly string  $txid,
        public readonly ?Carbon  $time,
        public readonly bool    $success,
        public readonly ?int     $blockNumber,
        public readonly string  $from,
        public readonly string  $to,
        public readonly Decimal $value,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'txid' => $this->txid,
            'time' => $this->time->toDateTimeString(),
            'success' => $this->success,
            'blockNumber' => $this->blockNumber,
            'from' => $this->from,
            'to' => $this->to,
            'value' => $this->value->toString(),
        ];
    }

    public static function fromArray(array $data): ?static
    {
        if( ($data['raw_data']['contract'][0]['type'] ?? null) !== 'TransferContract' ) {
            return null;
        }
        $value = $data['raw_data']['contract'][0]['parameter']['value']['amount'];

        return new static(
            data: $data,
            txid: $data['txID'],
            time: isset($data['block_timestamp']) ? Date::createFromTimestampMs($data['block_timestamp']) : null,
            success: $data['ret'][0]['contractRet'] === 'SUCCESS',
            blockNumber: $data['blockNumber'] ?? null,
            from: AddressHelper::toBase58($data['raw_data']['contract'][0]['parameter']['value']['owner_address']),
            to: AddressHelper::toBase58($data['raw_data']['contract'][0]['parameter']['value']['to_address']),
            value: AmountHelper::sunToDecimal($value)
        );
    }
}
