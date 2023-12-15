<?php

namespace Mollsoft\LaravelTronModule\Concerns;

use FurqanSiddiqui\BIP39\BIP39;

trait Mnemonic
{
    public function mnemonicGenerate(int $wordCount = 15): array
    {
        $mnemonic = BIP39::Generate($wordCount);

        return $mnemonic->words;
    }

    public function mnemonicValidate(string|array $mnemonic): bool
    {
        if (!is_array($mnemonic)) {
            $mnemonic = explode(' ', $mnemonic);
        }

        try {
            BIP39::Words($mnemonic);
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    public function mnemonicSeed(string|array $mnemonic, string $passphrase = null): string
    {
        if (!is_array($mnemonic)) {
            $mnemonic = explode(' ', $mnemonic);
        }

        $mnemonic = BIP39::Words($mnemonic);
        return bin2hex($mnemonic->generateSeed((string)$passphrase));
    }
}
