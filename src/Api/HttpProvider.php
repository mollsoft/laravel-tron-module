<?php

namespace Mollsoft\LaravelTronModule\Api;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mollsoft\LaravelTronModule\Api\Enums\HttpMethod;

class HttpProvider
{
    public function __construct(
        public readonly string     $baseUri,
        protected readonly array   $headers = [],
        protected readonly ?string $user = null,
        protected readonly ?string $password = null,
        public readonly int        $timeout = 30000,
        protected string           $statusPage = '/'
    )
    {
    }

    public function setStatusPage(string $page = '/'): void
    {
        $this->statusPage = $page;
    }

    public function isConnected(): bool
    {
        $response = $this->request($this->statusPage);

        if (array_key_exists('blockID', $response)) {
            return true;
        } elseif (array_key_exists('status', $response)) {
            return true;
        } elseif (isset($response['database']['block'])) {
            return true;
        } elseif ($response['ok'] ?? false) {
            return true;
        }

        return false;
    }

    public function request(string $path, array $payload = [], HttpMethod $method = HttpMethod::GET): array
    {
        $client = Http::asJson()
            ->acceptJson()
            ->withOptions([
                'base_uri' => $this->baseUri,
                'timeout' => $this->timeout,
                'auth' => $this->user && [$this->user, $this->password]
            ])
            ->withHeaders($this->headers);

        $response = $method === HttpMethod::POST ? $client->post($path, $payload) : $client->get($path);
        $statusCode = $response->status();
        $body = $response->body();

        if ((string)$response->getBody() === 'OK') {
            $body = 'OK';
        }

        return $this->decode($body, $statusCode);
    }

    protected function decode(string $body, int $status): array
    {
        $decodedBody = json_decode($body, true);

        if ($body == 'OK') {
            $decodedBody = [
                'status' => 1
            ];
        } elseif ($decodedBody == null or !is_array($decodedBody)) {
            $decodedBody = [];
        }

        if ($status == 404) {
            throw new \Exception('Page not found');
        }

        if (isset($decodedBody['Error'])) {
            throw new \Exception($decodedBody['Error']);
        }

        return $decodedBody;
    }
}
