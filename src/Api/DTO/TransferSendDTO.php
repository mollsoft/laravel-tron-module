<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;

class TransferSendDTO
{
    public function __construct(
        public readonly string $txid,
        public readonly TransferPreviewDTO $preview,
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
