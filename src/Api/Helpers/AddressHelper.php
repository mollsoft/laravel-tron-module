<?php

namespace Mollsoft\LaravelTronModule\Api\Helpers;

use Mollsoft\LaravelTronModule\Api\Support\Base58Check;

class AddressHelper
{
    public static function toHex(string $address): string
    {
        if (strlen($address) === 42 && mb_strpos($address, '41') === 0) {
            return $address;
        }

        return Base58Check::decode($address, 0, 3);
    }

    public static function toBase58(string $address): string
    {
        if (!ctype_xdigit($address)) {
            return $address;
        }

        if (strlen($address) < 2 || (strlen($address) & 1) != 0) {
            return '';
        }

        return Base58Check::encode($address, 0, false);
    }
}
