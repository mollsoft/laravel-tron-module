<?php

namespace Mollsoft\LaravelTronModule\Api;

use Brick\Math\BigDecimal;
use Mollsoft\LaravelTronModule\Api\Helpers\AddressHelper;
use Mollsoft\LaravelTronModule\Api\Helpers\AmountHelper;
use phpseclib\Math\BigInteger;
use Web3\Contracts\Ethabi;
use Web3\Contracts\Types\Address;
use Web3\Contracts\Types\Boolean;
use Web3\Contracts\Types\Bytes;
use Web3\Contracts\Types\DynamicBytes;
use Web3\Contracts\Types\Integer;
use Web3\Contracts\Types\Str;
use Web3\Contracts\Types\Uinteger;

class TRC20Contract
{
    protected readonly array $abiData;

    protected ?string $name = null;
    protected ?string $symbol = null;
    protected ?int $decimals = null;

    public function __construct(
        protected readonly ApiManager $manager,
        public readonly string $address,
    ) {
        $this->abiData = json_decode(file_get_contents(__DIR__.'/Resources/trc20.json'), true);
    }

    public function name(): string
    {
        if ($this->name === null) {
            $trigger = $this->triggerConstantContract('name');
            $value = $trigger[0] ?? null;
            if (!$value) {
                throw new \Exception('Error receive TRC20 contract - name');
            }
            $this->name = $trigger[0];
        }

        return $this->name;
    }

    public function symbol(): string
    {
        if ($this->symbol === null) {
            $trigger = $this->triggerConstantContract('symbol');
            $value = $trigger[0] ?? null;
            if (!$value) {
                throw new \Exception('Error receive TRC20 contract - symbol');
            }
            $this->symbol = $trigger[0];
        }

        return $this->symbol;
    }

    public function decimals(): int
    {
        if ($this->decimals === null) {
            $trigger = $this->triggerConstantContract('decimals');
            $value = $trigger[0] ?? null;
            if (!($value instanceof BigInteger)) {
                throw new \Exception('Error receive TRC20 contract - decimals');
            }
            $this->decimals = intval($value->value);
        }

        return $this->decimals;
    }

    public function balanceOf(string $address): BigDecimal
    {
        $address = AddressHelper::toBase58($address);
        $addressHex = AddressHelper::toHex($address);
        $trigger = $this->triggerConstantContract('balanceOf', [
            str_pad($addressHex, 64, "0", STR_PAD_LEFT)
        ], $address);
        $value = $trigger[0] ?? null;
        if (!($value instanceof BigInteger)) {
            throw new \Exception('Failed to retrieve TRC20 token balance of address "'.$address.'"');
        }

        return AmountHelper::toDecimal($value->toString(), $this->decimals());
    }

    protected function getAbiFunction(string $name): array
    {
        foreach ($this->abiData as $item) {
            if (($item['name'] ?? null) === $name) {
                return $item;
            }
        }

        throw new \Exception('Function '.$name.' not found in ABI');
    }

    public function triggerConstantContract(
        string $function,
        array $params = null,
        string $ownerAddress = null,
        bool $raw = false
    ): array {
        if ($params === null) {
            $params = [];
        }
        $ownerAddress = $ownerAddress ? AddressHelper::toHex(
            $ownerAddress
        ) : '410000000000000000000000000000000000000000';
        $contractAddress = AddressHelper::toHex($this->address);

        $abiFunction = $this->getAbiFunction($function);
        if (count($abiFunction['inputs']) !== count($params)) {
            throw new \Exception('For function '.$function.' params count must be '.count($abiFunction['inputs']));
        }

        $inputs = array_map(fn($item) => $item['type'], $abiFunction['inputs']);
        $functionSelector = $abiFunction['name'].'('.implode(',', $inputs).')';

        $ethAbi = new Ethabi([
            'address' => new Address,
            'bool' => new Boolean,
            'bytes' => new Bytes,
            'dynamicBytes' => new DynamicBytes,
            'int' => new Integer,
            'string' => new Str,
            'uint' => new Uinteger,
        ]);
        $parameters = substr($ethAbi->encodeParameters($abiFunction, $params), 2);

        $data = $this->manager->request('wallet/triggerconstantcontract', null, [
            'owner_address' => $ownerAddress,
            'contract_address' => $contractAddress,
            'function_selector' => $functionSelector,
            'parameter' => $parameters,
        ]);

        if (!($data['result'] ?? null)) {
            throw new \Exception(json_encode($data));
        }
        if (!($data['result']['result'] ?? null)) {
            $message = isset($data['result']['message']) ? hex2bin($data['result']['message']) : null;
            throw new \Exception($message ?: json_encode($data));
        }

        if ($raw) {
            return $data;
        }

        if (count($abiFunction['outputs']) >= 0 && isset($data['constant_result'][0])) {
            return $ethAbi->decodeParameters($abiFunction, $data['constant_result'][0]);
        }

        return $data['transaction'];
    }

    public function triggerSmartContract(
        string $function,
        array $params = null,
        string $ownerAddress = null,
        string|int|float|BigDecimal $feeLimit = 1,
        string|int|float|BigDecimal $cellValue = 0,
        bool $raw = false
    ): array {
        $feeLimit = AmountHelper::decimalToSun($feeLimit);
        $cellValue = AmountHelper::decimalToSun($cellValue);

        if ($params === null) {
            $params = [];
        }
        $ownerAddress = $ownerAddress ? AddressHelper::toHex(
            $ownerAddress
        ) : '410000000000000000000000000000000000000000';
        $contractAddress = AddressHelper::toHex($this->address);

        $abiFunction = $this->getAbiFunction($function);
        if (count($abiFunction['inputs']) !== count($params)) {
            throw new \Exception('For function '.$function.' params count must be '.count($abiFunction['inputs']));
        }

        $inputs = array_map(fn($item) => $item['type'], $abiFunction['inputs']);
        $functionSelector = $abiFunction['name'].'('.implode(',', $inputs).')';

        $ethAbi = new Ethabi([
            'address' => new Address,
            'bool' => new Boolean,
            'bytes' => new Bytes,
            'dynamicBytes' => new DynamicBytes,
            'int' => new Integer,
            'string' => new Str,
            'uint' => new Uinteger,
        ]);
        $parameters = substr($ethAbi->encodeParameters($abiFunction, $params), 2);

        $data = $this->manager->request('wallet/triggersmartcontract', null, [
            'owner_address' => $ownerAddress,
            'contract_address' => $contractAddress,
            'function_selector' => $functionSelector,
            'parameter' => $parameters,
            'fee_limit' => $feeLimit,
            'call_value' => $cellValue,
        ]);

        if (!($data['result'] ?? null)) {
            throw new \Exception(json_encode($data));
        }
        if (!($data['result']['result'] ?? null)) {
            $message = isset($data['result']['message']) ? hex2bin($data['result']['message']) : null;
            throw new \Exception($message ?: json_encode($data));
        }

        if ($raw) {
            return $data;
        }

        if (count($abiFunction['outputs']) >= 0 && isset($data['constant_result'][0])) {
            return $ethAbi->decodeParameters($abiFunction, $data['constant_result'][0]);
        }

        return $data['transaction'];
    }
}
