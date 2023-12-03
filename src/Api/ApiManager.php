<?php

namespace Mollsoft\LaravelTronModule\Api;

use Mollsoft\LaravelTronModule\Api\Enums\HttpMethod;

class ApiManager
{
    protected array $defaults = [
        'fullNode' => [
            'url' => 'https://api.trongrid.io',
            'statusPage' => 'wallet/getnowblock',
        ],
        'solidityNode' => [
            'url' => 'https://api.trongrid.io',
            'statusPage' => 'walletsolidity/getnowblock',
        ],
        'eventServer' => [
            'url' => 'https://api.trongrid.io',
            'statusPage' => 'healthcheck',
        ],
        'explorer' => [
            'url' => 'https://apilist.tronscan.org',
            'statusPage' => 'api/system/status',
        ],
        'signServer' => null,
    ];

    public readonly array $providers;

    public function __construct(array $providers)
    {
        foreach ($this->defaults as $provider => $default) {
            $current = $providers[$provider] ?? null;
            if (!($current instanceof HttpProvider)) {
                $url = is_string($current) ? $current : ($default['url'] ?? null);
                $current = $url ? new HttpProvider($url) : null;
            }

            if ($current) {
                $current->setStatusPage($default['statusPage'] ?? null);
            }

            $providers[$provider] = $current;
        }

        foreach ($providers as $provider => $current) {
            if ($current !== null && !($current instanceof HttpProvider)) {
                throw new \Exception('Unknown provider ' . $provider);
            }
        }

        $this->providers = $providers;
    }

    public function getProvider(string $provider, string $name): HttpProvider
    {
        if (!isset($this->providers[$provider])) {
            throw new \Exception($name . ' is not activated.');
        }

        return $this->providers[$provider];
    }

    public function fullNode(): HttpProvider
    {
        return $this->getProvider('fullNode', 'Full node');
    }

    public function solidityNode(): HttpProvider
    {
        return $this->getProvider('solidityNode', 'Solidity node');
    }

    public function eventServer(): HttpProvider
    {
        return $this->getProvider('eventServer', 'Event server');
    }

    public function explorer(): HttpProvider
    {
        return $this->getProvider('explorer', 'Explorer');
    }

    public function signServer(): HttpProvider
    {
        return $this->getProvider('signServer', 'Sign server');
    }

    public function isConnected(): array
    {
        $array = [];
        foreach ($this->providers as $provider => $current) {
            $array[] = [
                $provider => boolval($current->isConnected())
            ];
        }

        return $array;
    }

    public function request(string $path, array $get = null, array $post = null): array
    {
        $provider = $this->fullNode();

        $split = explode('/', $path);
        if (in_array($split[0], ['walletsolidity', 'walletextension'])) {
            $provider = $this->solidityNode();
        } elseif (in_array($split[0], ['event'])) {
            $provider = $this->eventServer();
        } elseif (in_array($split[0], ['trx-sign'])) {
            $provider = $this->signServer();
        } elseif (in_array($split[0], ['api'])) {
            $provider = $this->explorer();
        }

        if ($get) {
            $path .= '?' . http_build_query($get);
        }

        return $provider->request($path, $post ?? [], $post !== null ? HttpMethod::POST : HttpMethod::GET);
    }
}
