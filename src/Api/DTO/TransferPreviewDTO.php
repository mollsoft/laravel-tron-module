<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;

use Decimal\Decimal;

class TransferPreviewDTO
{
    public function __construct(
        public readonly ?string $error,
        public readonly ?AccountDTO $from,
        public readonly ?AccountResourcesDTO $fromResources,
        public readonly ?AccountDTO $to,
        public readonly ?Decimal $balanceBefore,
        public readonly ?Decimal $balanceAfter,
        public readonly ?Decimal $activateFee,
        public readonly ?array $transaction,
        public readonly ?int $bandwidthRequired = null,
        public readonly ?int $bandwidthBefore = null,
        public readonly ?int $bandwidthAfter = null,
        public readonly ?Decimal $bandwidthFee = null,
    )
    {}

    public function hasError(): bool
    {
        return !!$this->error;
    }

    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'from' => $this->from?->toArray(),
            'fromResources' => $this->fromResources?->toArray(),
            'to' => $this->to?->toArray(),
            'balance' => [
                'before' => $this->balanceBefore?->toString(),
                'after' => $this->balanceAfter?->toString(),
            ],
            'activateFee' => $this->activateFee?->toString(),
            'bandwidth' => [
                'required' => $this->bandwidthRequired,
                'before' => $this->bandwidthBefore,
                'after' => $this->bandwidthAfter,
                'fee' => $this->bandwidthFee?->toString(),
            ]
        ];
    }
}
