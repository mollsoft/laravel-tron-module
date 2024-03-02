<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use Decimal\Decimal;
use Mollsoft\LaravelTronModule\Api\DTO\TransferPreviewDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TransferSendDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferSendDTO;
use Mollsoft\LaravelTronModule\Models\TronAddress;
use Mollsoft\LaravelTronModule\Models\TronTRC20;

trait Transfer
{
    public function previewTransfer(TronAddress $from, string $to, Decimal|float|int|string $amount): TransferPreviewDTO
    {
        return $from
            ->wallet
            ->node
            ->api()
            ->transfer($from->address, $to, $amount)
            ->preview();
    }

    public function transfer(TronAddress $from, string $to, Decimal|float|int|string $amount): TransferSendDTO
    {
        return $from
            ->wallet
            ->node
            ->api()
            ->transfer($from->address, $to, $amount)
            ->send($from->private_key);
    }

    public function transferAll(TronAddress $from, string $to): TransferSendDTO
    {
        $api = $from->wallet->node->api();

        $preview = $api
            ->transfer($from->address, $to, 1)
            ->preview();
        if ($preview->hasError()) {
            throw new \Exception($preview->error);
        }

        return $api
            ->transfer(
                $from->address,
                $to,
                $preview->balanceBefore - ($preview->bandwidthFee ?? 0) - ($preview->activateFee ?? 0)
            )
            ->send($from->private_key);
    }

    public function transferTRC20(TronTRC20 $trc20, TronAddress $from, string $to, Decimal|float|int|string $amount): TRC20TransferSendDTO
    {
        return $from
            ->wallet
            ->node
            ->api()
            ->transferTRC20($trc20->address, $from->address, $to, $amount)
            ->send($from->private_key);
    }

    public function transferTRC20All(TronTRC20 $trc20, TronAddress $from, string $to): TRC20TransferSendDTO
    {
        $api = $from->wallet->node->api();

        $preview = $api
            ->transferTRC20($trc20->address, $from->address, $to, 1)
            ->preview();
        if ($preview->hasError()) {
            throw new \Exception($preview->error);
        }

        return $api
            ->transferTRC20($trc20->address, $from->address, $to, $preview->tokenBefore)
            ->send($from->private_key);
    }
}