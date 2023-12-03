<?php

namespace Mollsoft\LaravelTronModule\Api\Methods;

use Decimal\Decimal;
use kornrunner\Secp256k1;
use kornrunner\Signature\Signature;
use Mollsoft\LaravelTronModule\Api\Api;
use Mollsoft\LaravelTronModule\Api\DTO\TransferPreviewDTO;
use Mollsoft\LaravelTronModule\Api\DTO\TransferSendDTO;
use Mollsoft\LaravelTronModule\Api\Helpers\AddressHelper;
use Mollsoft\LaravelTronModule\Api\Helpers\AmountHelper;

class Transfer
{
    protected ?TransferPreviewDTO $preview = null;

    public function __construct(
        protected readonly Api  $api,
        public readonly string  $from,
        public readonly string  $to,
        public readonly Decimal $amount,
    )
    {
    }

    public function preview(): TransferPreviewDTO
    {
        if( $this->preview !== null ) {
            return $this->preview;
        }

        $error = null;
        $from = null;
        $fromResources = null;
        $to = null;
        $balanceBefore = null;
        $balanceAfter = null;
        $activateFee = null;
        $transaction = null;
        $bandwidthRequired = null;
        $bandwidthBefore = null;
        $bandwidthAfter = null;
        $bandwidthFee = null;

        try {
            $from = $this->api->getAccount($this->from);
            $fromResources = $this->api->getAccountResources($this->from);
            $to = $this->api->getAccount($this->to);

            if (!$from->activated) {
                throw new \Exception('From Address is not activated');
            }

            $balanceBefore = $from->balance;
            $balanceAfter = $balanceBefore->sub($this->amount);

            if (!$to->activated) {
                $activateFee = AmountHelper::sunToDecimal(100000);
                $balanceAfter = $balanceAfter->sub($activateFee);
            }
            if ($balanceAfter < 0) {
                throw new \Exception('Insufficient balance');
            }

            $transaction = $this->api->manager->request('wallet/createtransaction', null, [
                'owner_address' => AddressHelper::toHex($this->from),
                'to_address' => AddressHelper::toHex($this->to),
                'amount' => AmountHelper::decimalToSun($this->amount),
            ]);

            $bandwidthRequired = $to->activated ? strlen($transaction['raw_data_hex']) + 1 : 0;
            $bandwidthBefore = $fromResources->bandwidthAvailable;
            if( $bandwidthRequired > $bandwidthBefore ) {
                $bandwidthFee = AmountHelper::sunToDecimal(($bandwidthRequired + 1) * 1000);
                $balanceAfter = $balanceAfter->sub($bandwidthFee);
            }
            else {
                $bandwidthFee = new Decimal(0);
                $bandwidthAfter = $bandwidthBefore - $bandwidthRequired;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $this->preview = new TransferPreviewDTO(
            error: $error,
            from: $from,
            fromResources: $fromResources,
            to: $to,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            activateFee: $activateFee,
            transaction: $transaction,
            bandwidthRequired: $bandwidthRequired,
            bandwidthBefore: $bandwidthBefore,
            bandwidthAfter: $bandwidthAfter,
            bandwidthFee: $bandwidthFee
        );

        return $this->preview;
    }

    public function send(string $privateKey): TransferSendDTO
    {
        $preview = $this->preview();
        if( $preview->hasError() ) {
            throw new \Exception($preview->error);
        }

        $transaction = $this->api->signTransaction($preview->transaction, $privateKey);

        $data = $this->api->manager->request('wallet/broadcasttransaction', null, $transaction);
        if (!isset($data['txid'])) {
            throw new \Exception($response['Error'] ?? print_r($data, true));
        }

        return new TransferSendDTO(
            txid: $data['txid'],
            preview: $preview,
        );
    }
}
