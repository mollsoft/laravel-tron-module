<?php

namespace Mollsoft\LaravelTronModule\Wallet;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Hash;
use Mollsoft\LaravelTronModule\Models\TronWallet;

class Encrypted
{
    protected Encrypter $encrypter;
    protected ?string $password = null;
    protected ?string $mnemonic = null;
    protected ?string $seed = null;

    public function __construct(protected readonly TronWallet $wallet)
    {
    }

    public function encrypt(string $password): void
    {
        $this->password = $password;
        $this->wallet->password = Hash::make($password);
        $this->encrypter = new Encrypter(md5($password), 'aes-256-cbc');

        $this->mnemonic = $this->wallet->mnemonic;
        $this->seed = $this->wallet->seed;

        $this->wallet->mnemonic = $this->encrypter->encrypt($this->wallet->mnemonic);
        $this->wallet->seed = $this->encrypter->encrypt($this->wallet->seed);
    }

    public function unlock(string $password): bool
    {
        if (!Hash::check($password, $this->wallet->password)) {
            return false;
        }

        $this->password = $password;
        $this->encrypter = new Encrypter(md5($password), 'aes-256-cbc');

        $this->mnemonic = $this->encrypter->decrypt($this->wallet->mnemonic);
        $this->seed = $this->encrypter->decrypt($this->wallet->seed);

        return true;
    }

    public function encode(mixed $value): string
    {
        return $this->encrypter->encrypt($value);
    }

    public function decode(string $value): mixed
    {
        return $this->encrypter->decrypt($value);
    }

    public function lock(): void
    {
        $this->password = null;
        $this->mnemonic = null;
        $this->seed = null;
    }

    public function isUnlocked(): bool
    {
        return !!$this->password;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    public function mnemonic(): ?string
    {
        return $this->mnemonic;
    }

    public function seed(): ?string
    {
        return $this->seed;
    }
}
