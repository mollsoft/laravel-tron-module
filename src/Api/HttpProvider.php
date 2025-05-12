<?php

namespace Mollsoft\LaravelTronModule\Api;

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
        protected string           $statusPage = '/',
        protected readonly ?string $proxy = null
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
                'auth' => $this->user && [$this->user, $this->password],
                'proxy' => $this->formatProxy($this->proxy),
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

    protected function formatProxy(?string $proxy): ?string
    {
        if (!$proxy) {
            return null;
        }

        // Проверяем формат прокси (добавляем поддержку логина и пароля)
        if (preg_match('/^(socks4|socks5|https?|http):\/\/(([^:]+):([^@]+)@)?([^:\/]+)(:\d+)?$/', $proxy, $matches)) {
            $protocol = $matches[1];
            $username = $matches[3] ?? null;
            $password = $matches[4] ?? null;
            $host = $matches[5];
            $port = $matches[6] ?? '';

            // Поддержка socks4 и socks5 (с аутентификацией или без)
            if (in_array($protocol, ['socks4', 'socks5'])) {
                if ($username && $password) {
                    return "{$protocol}://{$username}:{$password}@{$host}{$port}";
                }
                return "{$protocol}://{$host}{$port}";
            }

            // Поддержка http, https
            if ($username && $password) {
                return "{$protocol}://{$username}:{$password}@{$host}{$port}";
            }

            return "{$protocol}://{$host}{$port}";
        }

        throw new \InvalidArgumentException('Invalid proxy format. Supported formats: socks4|socks5|http|https.');
    }
}
