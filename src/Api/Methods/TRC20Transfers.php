<?php

namespace Mollsoft\LaravelTronModule\Api\Methods;

use Mollsoft\LaravelTronModule\Api\ApiManager;
use Mollsoft\LaravelTronModule\Api\DTO\TRC20TransferDTO;
use Mollsoft\LaravelTronModule\Api\Enums\Confirmation;
use Mollsoft\LaravelTronModule\Api\Enums\OrderBy;

class TRC20Transfers implements \Iterator
{
    protected ?bool $onlyConfirmation = null;
    protected int $limit = 20;
    protected ?string $fingerprint = null;
    protected ?string $orderBy = null;
    protected ?int $minTimestamp = null;
    protected ?int $maxTimestamp = null;
    protected ?string $contractAddress = null;
    protected array $collection = [];
    protected bool $hasNext = true;
    protected int $current = 0;

    public function __construct(
        protected readonly ApiManager $manager,
        public readonly string $address,
    ) {
    }

    public function onlyConfirmation(?Confirmation $confirmed): static
    {
        $this->onlyConfirmation = $confirmed ? $confirmed->name === 'CONFIRMED' : null;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = max(min($limit, 200), 1);

        return $this;
    }

    public function fingerprint(?string $fingerprint): static
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    public function orderBy(?OrderBy $orderBy): static
    {
        $this->orderBy = $orderBy ? 'block_timestamp,'.$orderBy->value : null;

        return $this;
    }

    public function minTimestamp(?int $minTimestamp): static
    {
        $this->minTimestamp = $minTimestamp;

        return $this;
    }

    public function maxTimestamp(?int $maxTimestamp): static
    {
        $this->maxTimestamp = $maxTimestamp;

        return $this;
    }

    public function contractAddress(?string $contractAddress): static
    {
        $this->contractAddress = $contractAddress;

        return $this;
    }

    public function getQuery(): array
    {
        $query = [
            'limit' => $this->limit,
            'fingerprint' => $this->fingerprint,
            'order_by' => $this->orderBy,
            'min_timestamp' => $this->minTimestamp,
            'max_timestamp' => $this->maxTimestamp,
            'contract_address' => $this->contractAddress,
        ];

        if ($this->onlyConfirmation !== null) {
            $query[$this->onlyConfirmation ? 'only_confirmed' : 'only_unconfirmed'] = true;
        }

        return array_filter($query, fn($item) => $item !== null);
    }

    public function current(): TRC20TransferDTO
    {
        return $this->collection[$this->current];
    }

    public function next(): void
    {
        $this->current++;
    }

    public function key(): int
    {
        return $this->current;
    }

    public function valid(): bool
    {
        if (isset($this->collection[$this->current])) {
            return true;
        }

        if (!$this->hasNext) {
            return false;
        }

        $this->collection = array_values([
            ...$this->collection,
            ...$this->request(),
        ]);

        return isset($this->collection[$this->current]);
    }

    public function rewind(): void
    {
        $this->collection = $this->request();

        $this->current = 0;
    }

    protected function request(): array
    {
        $data = $this->manager->request(
            'v1/accounts/'.$this->address.'/transactions/trc20',
            $this->getQuery()
        );

        $this->fingerprint = $data['meta']['fingerprint'] ?? null;
        $this->hasNext = !!$this->fingerprint;

        return array_values(
            array_filter(
                array_map(
                    fn(array $item) => TRC20TransferDTO::fromArray($item),
                    $data['data']
                )
            )
        );
    }
}
