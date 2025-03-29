<?php

namespace Mollsoft\LaravelTronModule\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;

class EncryptedCast implements CastsAttributes
{
    protected function encrypter($model): ?Encrypter
    {
        $password = $model->plain_password;
        if( !$password && $model->password ) {
            $password = $model->password;
        }

        $key = hash('sha256', $password.'::'.config('app.key'), true);
        return new Encrypter($key, 'AES-256-CBC');
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value) return null;

        return $this->encrypter($model)->decryptString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value ? $this->encrypter($model)->encryptString($value) : null;
    }
}
