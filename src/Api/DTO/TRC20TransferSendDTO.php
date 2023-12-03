<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;

class TRC20TransferSendDTO
{
    public function __construct(
        public readonly string $txid,
        public readonly TRC20TransferPreviewDTO $preview,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'txid' => $this->txid,
            'preview' => $this->preview->toArray(),
        ];
    }
}
