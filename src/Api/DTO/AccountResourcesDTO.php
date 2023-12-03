<?php

namespace Mollsoft\LaravelTronModule\Api\DTO;


class AccountResourcesDTO
{
    public function __construct(
        public readonly string $address,
        public readonly array  $data,
        public readonly bool   $activated,
        public readonly ?int $bandwidthTotal,
        public readonly ?int $bandwidthUsed,
        public readonly ?int $bandwidthAvailable,
        public readonly ?int $energyTotal,
        public readonly ?int $energyUsed,
        public readonly ?int $energyAvailable,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'activated' => $this->activated,
            'bandwidth' => [
                'total' => $this->bandwidthTotal,
                'used' => $this->bandwidthUsed,
                'available' => $this->bandwidthAvailable,
            ],
            'energy' => [
                'total' => $this->energyTotal,
                'used' => $this->energyUsed,
                'available' => $this->energyAvailable,
            ]
        ];
    }

    public static function fromArray(string $address, array $data): static
    {
        $activated = count($data) > 0;

        return new static(
            address: $address,
            data: $data,
            activated: $activated,
            bandwidthTotal: !$activated ? null : $data['freeNetLimit'] ?? 0,
            bandwidthUsed: !$activated ? null : $data['freeNetUsed'] ?? 0,
            bandwidthAvailable: !$activated ? null : ($data['freeNetLimit'] ?? 0) - ($data['freeNetUsed'] ?? 0),
            energyTotal: !$activated ? null : $data['EnergyLimit'] ?? 0,
            energyUsed: !$activated ? null : $data['EnergyUsed'] ?? 0,
            energyAvailable: !$activated ? null : ($data['EnergyLimit'] ?? 0) - ($data['EnergyUsed'] ?? 0),
        );
    }
}
