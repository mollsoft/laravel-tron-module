<?php

namespace Mollsoft\LaravelTronModule\Services;

use Closure;

abstract class BaseSync
{
    protected ?Closure $logger = null;
    protected float $startedAt;

    public function setLogger(?Closure $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    protected function log(string $message, ?string $type = null): void
    {
        if ($this->logger) {
            call_user_func($this->logger, '['.round((microtime(true) - $this->startedAt), 4).' s] '.$message, $type);
        }
    }

    public function run(): void
    {
        $this->startedAt = microtime(true);
    }

}