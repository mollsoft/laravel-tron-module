<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Brick\Math\BigDecimal;
use Mollsoft\LaravelTronModule\Api\DTO\TransferPreviewDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TransferSendDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferPreviewDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferSendDTO;
use Mollsoft\LaravelTronModule\Facades\Tron;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronTRC20;

trait Transfer
{
    public function previewTransfer(
        TronAddress $from,
        string $to,
        BigDecimal|float|int|string $amount,
        BigDecimal|float|int|string|null $balance = null,
        ?int $bandwidth = null
    ): TransferPreviewDTO
    {
        $node = $from->wallet->node ?? Tron::getNode();
        $node->increment('requests', 4);

        return $node
            ->api()
            ->transfer($from->address, $to, $amount)
            ->preview($balance, $bandwidth);
    }

    public function transfer(TronAddress $from, string $to, BigDecimal|float|int|string $amount): TransferSendDTO
    {
        $node = $from->wallet->node ?? Tron::getNode();
        $node->increment('requests', 5);

        return $node
            ->api()
            ->transfer($from->address, $to, $amount)
            ->send($from->private_key);
    }

    public function transferAll(TronAddress $from, string $to): TransferSendDTO
    {
        $node = $from->wallet->node ?? Tron::getNode();
        $node->increment('requests', 4);

        $preview = $node->api()
            ->transfer($from->address, $to, 1)
            ->preview();
        if ($preview->hasError()) {
            throw new \Exception($preview->error);
        }

        $node->increment('requests', 5);

        return $node->api()
            ->transfer(
                $from->address,
                $to,
                $preview->balanceBefore - ($preview->bandwidthFee ?? 0) - ($preview->activateFee ?? 0)
            )
            ->send($from->private_key);
    }

    public function transferTRC20(
        TronTRC20 $trc20,
        TronAddress $from,
        string $to,
        BigDecimal|float|int|string $amount,
        string|int|float|BigDecimal $feeLimit = 30,
    ): TRC20TransferSendDTO
    {
        $node = $from->wallet->node ?? Tron::getNode();
        $node->increment('requests', 6);

        return $node
            ->api()
            ->transferTRC20($trc20->address, $from->address, $to, $amount, $feeLimit)
            ->send($from->private_key);
    }

    public function previewTransferTRC20(
        TronTRC20 $trc20,
        TronAddress $from,
        string $to,
        BigDecimal|float|int|string $amount,
        BigDecimal|float|int|string|null $balance = null,
        BigDecimal|float|int|string|null $tokenBalance = null,
        ?int $bandwidth = null,
        ?int $energy = null,
        string|int|float|BigDecimal $feeLimit = 30,
    ): TRC20TransferPreviewDTO
    {
        $node = $from->wallet->node ?? Tron::getNode();
        $node->increment('requests', 5);

        return $node
            ->api()
            ->transferTRC20($trc20->address, $from->address, $to, $amount, $feeLimit)
            ->preview($balance, $tokenBalance, $bandwidth, $energy);
    }

    public function transferTRC20All(
        TronTRC20 $trc20,
        TronAddress $from,
        string $to,
        string|int|float|BigDecimal $feeLimit = 30
    ): TRC20TransferSendDTO
    {
        $node = $from->wallet->node ?? Tron::getNode();
        $node->increment('requests', 5);

        $preview = $node
            ->api()
            ->transferTRC20($trc20->address, $from->address, $to, 1, $feeLimit)
            ->preview();
        if ($preview->hasError()) {
            throw new \Exception($preview->error);
        }

        $node->increment('requests', 6);

        return $node
            ->api()
            ->transferTRC20($trc20->address, $from->address, $to, $preview->tokenBefore, $feeLimit)
            ->send($from->private_key);
    }
}
