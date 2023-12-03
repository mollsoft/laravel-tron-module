<?php

namespace Mollsoft\LaravelTronModule\Api\Methods;

use Decimal\Decimal;
use Mollsoft\LaravelTronModule\Api\Api;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferPreviewDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferSendDTO;
use Mollsoft\LaravelTronModule\Api\Helpers\AddressHelper;
use Mollsoft\LaravelTronModule\Api\Helpers\AmountHelper;
use Mollsoft\LaravelTronModule\Api\TRC20Contract;

class TRC20Transfer
{
    protected ?TRC20TransferPreviewDTO $preview = null;

    public function __construct(
        protected readonly Api        $api,
        public readonly TRC20Contract $contract,
        public readonly string        $from,
        public readonly string        $to,
        public readonly Decimal       $amount,
        public readonly Decimal       $feeLimit,
    )
    {
    }

    public function preview(): TRC20TransferPreviewDTO
    {
        if ($this->preview !== null) {
            return $this->preview;
        }

        $error = null;
        $from = null;
        $fromResources = null;
        $to = null;
        $balanceBefore = null;
        $balanceAfter = null;
        $tokenBefore = null;
        $tokenAfter = null;
        $activated = null;
        $energyDebug = null;
        $energyRequired = null;
        $energyBefore = null;
        $energyAfter = null;
        $energyInsufficient = null;
        $energyFee = null;
        $bandwidthDebug = null;
        $bandwidthRequired = null;
        $bandwidthBefore = null;
        $bandwidthAfter = null;
        $bandwidthInsufficient = null;
        $bandwidthFee = null;
        $transaction = null;

        try {
            $from = $this->api->getAccount($this->from);
            $fromResources = $this->api->getAccountResources($this->from);
            $to = $this->api->getAccount($this->to);

            if (!$from->activated) {
                throw new \Exception('From Address is not activated');
            }

            $balanceBefore = $from->balance;
            $balanceAfter = $balanceBefore->copy();

            $tokenBefore = $this->contract->balanceOf($this->from);
            $tokenAfter = $tokenBefore->sub($this->amount);

            if ($tokenAfter < 0) {
                throw new \Exception('Insufficient token balance');
            }

            $activated = $to->activated;

            $data = $this->contract->triggerConstantContract('transfer', [
                AddressHelper::toHex($this->to),
                AmountHelper::toSun($this->amount, $this->contract->decimals())
            ], $this->from, true);

            $energyDebug = $data;
            $energyRequired = $data['energy_used'];
            $energyBefore = $fromResources->energyAvailable;
            if ($energyRequired > $energyBefore) {
                $energyAfter = $energyBefore;
                $energyInsufficient = $energyRequired - $energyBefore;
                $energyPrice = $this->api->chainParameter('getEnergyFee', 420);
                $energyFee = AmountHelper::sunToDecimal($energyPrice)->mul($energyInsufficient);
                $balanceAfter = $balanceAfter->sub($energyFee);
            } else {
                $energyAfter = $energyBefore - $energyRequired;
            }

            $data = $this->contract->triggerSmartContract('transfer', [
                AddressHelper::toHex($this->to),
                AmountHelper::toSun($this->amount, $this->contract->decimals())
            ], $this->from, $this->feeLimit, 0, true);

            $bandwidthDebug = $data;
            $bandwidthRequired = strlen($data['transaction']['raw_data_hex']) + 1;
            $bandwidthBefore = $fromResources->bandwidthAvailable;
            if ($bandwidthRequired > $bandwidthBefore) {
                $bandwidthInsufficient = $bandwidthRequired;
                $bandwidthAfter = $bandwidthBefore;
                $bandwidthFee = AmountHelper::sunToDecimal(($bandwidthRequired + 1) * 1000);
                $balanceAfter = $balanceAfter->sub($bandwidthFee);
            } else {
                $bandwidthAfter = $bandwidthBefore - $bandwidthRequired;
            }
            $transaction = $data['transaction'];

            if( $this->feeLimit->sub($bandwidthFee ?: 0)->sub($energyFee ?: 0) < 0 ) {
                throw new \Exception('Fees over limit');
            }

            if ($balanceAfter < 0) {
                throw new \Exception('Insufficient balance');
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $this->preview = new TRC20TransferPreviewDTO(
            error: $error,
            from: $from,
            fromResources: $fromResources,
            to: $to,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            tokenBefore: $tokenBefore,
            tokenAfter: $tokenAfter,
            activated: $activated,
            energyDebug: $energyDebug,
            energyRequired: $energyRequired,
            energyBefore: $energyBefore,
            energyAfter: $energyAfter,
            energyInsufficient: $energyInsufficient,
            energyFee: $energyFee,
            bandwidthDebug: $bandwidthDebug,
            bandwidthRequired: $bandwidthRequired,
            bandwidthBefore: $bandwidthBefore,
            bandwidthAfter: $bandwidthAfter,
            bandwidthInsufficient: $bandwidthInsufficient,
            bandwidthFee: $bandwidthFee,
            transaction: $transaction
        );

        return $this->preview;
    }

    public function send(string $privateKey): TRC20TransferSendDTO
    {
        $preview = $this->preview();
        if ($preview->hasError()) {
            throw new \Exception($preview->error);
        }

        $transaction = $this->api->signTransaction($preview->transaction, $privateKey);

        $data = $this->api->manager->request('wallet/broadcasttransaction', null, $transaction);
        if (!isset($data['txid'])) {
            throw new \Exception($response['Error'] ?? print_r($data, true));
        }

        return new TRC20TransferSendDTO(
            txid: $data['txid'],
            preview: $preview,
        );
    }
}
