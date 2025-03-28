<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;


use Brick\Math\BigDecimal;

class TRC20TransferPreviewDTO
{
    public function __construct(
        public readonly ?string              $error,
        public readonly ?AccountDTO          $from,
        public readonly ?AccountResourcesDTO $fromResources,
        public readonly ?AccountDTO          $to,
        public readonly ?BigDecimal             $balanceBefore,
        public readonly ?BigDecimal             $balanceAfter,
        public readonly ?BigDecimal             $tokenBefore,
        public readonly ?BigDecimal             $tokenAfter,
        public readonly ?bool                $activated,
        public readonly ?array               $energyDebug,
        public readonly ?int                 $energyRequired,
        public readonly ?int                 $energyBefore,
        public readonly ?int                 $energyAfter,
        public readonly ?int                 $energyInsufficient,
        public readonly ?BigDecimal             $energyFee,
        public readonly ?array               $bandwidthDebug,
        public readonly ?int                 $bandwidthRequired,
        public readonly ?int                 $bandwidthBefore,
        public readonly ?int                 $bandwidthAfter,
        public readonly ?int                 $bandwidthInsufficient,
        public readonly ?BigDecimal             $bandwidthFee,
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
                'before' => $this->balanceBefore?->__toString(),
                'after' => $this->balanceAfter?->__toString(),
            ],
            'token' => [
                'before' => $this->tokenBefore?->__toString(),
                'after' => $this->tokenAfter?->__toString(),
            ],
            'activated' => $this->activated,
            'energy' => [
                'required' => $this->energyRequired,
                'before' => $this->energyBefore,
                'after' => $this->energyAfter,
                'insufficient' => $this->energyInsufficient,
                'fee' => $this->energyFee?->__toString(),
            ],
            'bandwidth' => [
                'required' => $this->bandwidthRequired,
                'before' => $this->bandwidthBefore,
                'after' => $this->bandwidthAfter,
                'insufficient' => $this->bandwidthInsufficient,
                'fee' => $this->bandwidthFee?->__toString(),
            ],
        ];
    }
}
