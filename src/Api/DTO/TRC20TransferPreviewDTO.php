<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;


use Decimal\Decimal;

class TRC20TransferPreviewDTO
{
    public function __construct(
        public readonly ?string              $error,
        public readonly ?AccountDTO          $from,
        public readonly ?AccountResourcesDTO $fromResources,
        public readonly ?AccountDTO          $to,
        public readonly ?Decimal             $balanceBefore,
        public readonly ?Decimal             $balanceAfter,
        public readonly ?Decimal             $tokenBefore,
        public readonly ?Decimal             $tokenAfter,
        public readonly ?bool                $activated,
        public readonly ?array               $energyDebug,
        public readonly ?int                 $energyRequired,
        public readonly ?int                 $energyBefore,
        public readonly ?int                 $energyAfter,
        public readonly ?int                 $energyInsufficient,
        public readonly ?Decimal             $energyFee,
        public readonly ?array               $bandwidthDebug,
        public readonly ?int                 $bandwidthRequired,
        public readonly ?int                 $bandwidthBefore,
        public readonly ?int                 $bandwidthAfter,
        public readonly ?int                 $bandwidthInsufficient,
        public readonly ?Decimal             $bandwidthFee,
        public readonly ?array               $transaction,
    )
    {
    }

    public function hasError(): bool
    {
        return !!$this->error;
    }

    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'from' => $this->from->toArray(),
            'fromResources' => $this->fromResources->toArray(),
            'to' => $this->to->toArray(),
            'balance' => [
                'before' => $this->balanceBefore?->toString(),
                'after' => $this->balanceAfter?->toString(),
            ],
            'token' => [
                'before' => $this->tokenBefore?->toString(),
                'after' => $this->tokenAfter?->toString(),
            ],
            'activated' => $this->activated,
            'energy' => [
                'required' => $this->energyRequired,
                'before' => $this->energyBefore,
                'after' => $this->energyAfter,
                'insufficient' => $this->energyInsufficient,
                'fee' => $this->energyFee?->toString(),
            ],
            'bandwidth' => [
                'required' => $this->bandwidthRequired,
                'before' => $this->bandwidthBefore,
                'after' => $this->bandwidthAfter,
                'insufficient' => $this->bandwidthInsufficient,
                'fee' => $this->bandwidthFee?->toString(),
            ],
        ];
    }
}
