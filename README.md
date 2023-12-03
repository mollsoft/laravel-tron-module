![Pest Laravel Expectations](https://banners.beyondco.de/Tron.png?theme=light&packageManager=composer+require&packageName=mollsoft%2Flaravel-tron-module&pattern=architect&style=style_1&description=Working+with+cryptocurrency+Tron%2C+supported+TRC-20+tokens&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

<a href="https://packagist.org/packages/mollsoft/laravel-tron-module" target="_blank">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/packagist/v/mollsoft/laravel-tron-module.svg?style=flat&cacheSeconds=3600" alt="Latest Version on Packagist">
</a>

<a href="https://www.php.net">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/badge/php-%3E=8.1-brightgreen.svg?maxAge=2592000" alt="Php Version">
</a>

<a href="https://laravel.com/">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/badge/laravel-%3E=10-red.svg?maxAge=2592000" alt="Php Version">
</a>

<a href="https://packagist.org/packages/mollsoft/laravel-tron-module" target="_blank">
    <img style="display: inline-block; margin-top: 0.5em; margin-bottom: 0.5em" src="https://img.shields.io/packagist/dt/mollsoft/laravel-tron-module.svg?style=flat&cacheSeconds=3600" alt="Total Downloads">
</a>

<a href="https://mollsoft.com"><img alt="Website" src="https://img.shields.io/badge/Website-https://mollsoft.com-black"></a>
<a href="https://t.me/mollsoft"><img alt="Telegram" src="https://img.shields.io/badge/Telegram-@mollsoft-blue"></a>

---

**Laravel Tron Module** is a Laravel package for work with cryptocurrency Tron, with the support TRC-20 tokens.It allows you to generate HD wallets using mnemonic phrase, validate addresses, get addresses balances and resources, preview and send TRX/TRC-20 tokens. You can automate the acceptance and withdrawal of cryptocurrency in your application.

You can contact me for help in integrating payment acceptance into your project.

## Examples

BIP39: Generate Mnemonic Phrase:
```php
$mnemonic = Tron::mnemonicGenerate(15);
print_r($mnemonic); // string[] length 15 words
```

BIP39: Validate Mnemonic Phrase:
```php
$mnemonic = 'record jelly ladder exotic hold access test minute target fortune duck disease express damp attend';
$isValid = Tron::mnemonicValidate($mnemonic);
echo $isValid ? 'OK' : 'ERROR';
```

BIP39: Get seed by Mnemonic Phrase:
```php
$mnemonic = 'record jelly ladder exotic hold access test minute target fortune duck disease express damp attend';
$mnemonicPassphrase = 'passphrase string';
$seed = Tron::mnemonicSeed($mnemonic, $mnemonicPassphrase);
echo $seed; // Seed in hex format
```

Create HD Wallet:
```php
$name = 'my-wallet';
$password = 'password for encrypt mnemonic, seed and private keys';
$mnemonic = 'record jelly ladder exotic hold access test minute target fortune duck disease express damp attend';
$mnemonicPassphrase = null;

$tronWallet = Tron::createWallet($name, $password, $mnemonic, $mnemonicPassphrase);
$tronWallet->save();
```

Unlock HD Wallet:
```php
$password = 'password for encrypt mnemonic, seed and private keys';

$tronWallet = TronWallet::first();
$isUnlocked = $tronWallet->encrypted()->unlock($password);
echo $isUnlocked ? 'WALLET UNLOCKED' : 'INCORRECT PASSWORD';

echo $tronWallet->plainMnemonic; // Print mnemonic phrase
```

Generate Address:
```php
$tronWallet = TronWallet::first();
$tronWallet->encrypted()->unlock($password);

$index = 0; // Address index (if null - automatic)
$tronAddress = Tron::createAddress($tronWallet, $index);
$tronAddress->save();

echo $tronAddress->address; // Print Address
echo $tronAddress->private_key; // Print private key
```

Import watch-only address:
```php
$wallet = TronWallet::first();
$address = Tron::importAddress($wallet, 'my tron address');
$address->save();
```

Add TRC-20 token for tracking:
```php
$contractAddress = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'; // Contract Address Tether USDT
$tronTRC20 = Tron::createTRC20($contractAddress);
$tronTRC20->save();

$balanceOfAddress = 'THPvaUhoh2Qn2y9THCZML3H815hhFhn5YC';
echo $tronTRC20->contract()->balanceOf($balanceOfAddress); // Get TRC-20 Token balance of address
```

Get Address info (balances + resources):
```php
$address = 'THPvaUhoh2Qn2y9THCZML3H815hhFhn5YC';

$getAccount = Tron::api()->getAccount($address);
print_r($getAccount->toArray());

$getAccountResources = Tron::api()->getAccountResources($address);
print_r($getAccountResources->toArray());
```

Validate Address:
```php
$address = 'THPvaUhoh2Qn2y9THCZML3H815hhFhn5YC';

$isValid = Tron::api()->validateAddress($address); // bool
```

Get Address Transfers (only TRX):
```php
$address = 'THPvaUhoh2Qn2y9THCZML3H815hhFhn5YC';

$transfers = Tron::api()->getTransfers($address)->limit(200);

foreach( $transfers as $transfer ) {
    print_r($transfer->toArray());
}
```

Get Address TRC-20 Transfers:
```php
$address = 'THPvaUhoh2Qn2y9THCZML3H815hhFhn5YC';

$transfers = Tron::api()->getTRC20Transfers($address)->limit(200);

foreach( $transfers as $transfer ) {
    print_r($transfer->toArray());
}
```

Get Transaction Info:
```php
$txid = '...';
$info = Tron::api()->getTransactionInfo($txid);
print_r($info->toArray());
```

Create TRX transfer, preview and send:
```php
$from = 'THPvaUhoh2Qn2y9THCZML3H815hhFhn5YC';
$to = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
$amount = 1;

$walletPassword = 'here wallet password';
$wallet = TronWallet::first();
$wallet->encrypted()->unlock($walletPassword);

$address = $wallet->addresses->first();
$privateKey = $wallet->encrypted()->decode($address->private_key);

$transfer = Tron::api()->transfer($from, $to, $amount);
$preview = $transfer->preview();
print_r($preview->toArray());

$send = $transfer->send($privateKey);
print_r($send->toArray());
```

Create TRC-20 transfer, preview and send:
```php
$contractAddress = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
$from = 'THPvaUhoh2Qn2y9THCZML3H815hhFhn5YC';
$to = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
$amount = 1;

$walletPassword = 'here wallet password';
$wallet = TronWallet::first();
$wallet->encrypted()->unlock($walletPassword);

$address = $wallet->addresses->first();
$privateKey = $wallet->encrypted()->decode($address->private_key);

$transfer = Tron::api()->transferTRC20($contractAddress, $from, $to, $amount);
$preview = $transfer->preview();
print_r($preview->toArray());

$send = $transfer->send($privateKey);
print_r($send->toArray());
```

## Helpers

Convert Base58 address to Hex:

```php
$addressBase58 = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
$addressHex = AddressHelper::toHex($addressBase58);
```

Convert Hex address to Base58:

```php
$addressHex = '11234....412412';
$addressBase58 = AddressHelper::toBase58($addressHex);
```

## Install

```bash
> composer require mollsoft/laravel-tron-module
> php artisan vendor:publish --tag=tron-config
> php artisan migrate
```

In file `app/Console/Kernel` in method `schedule(Schedule $schedule)` add 
```
$schedule->command('tron:scan')->everyMinute();
```

In .env file add:
```
TRONGRID_API_KEY="..."
```

## Commands

Scan transactions and update balances:

```bash
> php artisan tron:scan
```

Create TRC-20 Token:

```bash
> php artisan tron:new-trc20
```

Create Wallet:

```bash
> php artisan tron:new-wallet
```

Generate Address:

```bash
> php artisan tron:generate-address
```

## WebHook

You can set up a WebHook that will be called when a new incoming or outgoing TRX/TRC-20 transfer is detected.

In file config/tron.php you can set param:

```php
'webhook_handler' => \Mollsoft\LaravelTronModule\Handlers\EmptyWebhookHandler::class,
```

Example WebHook handler:

```php
class EmptyWebhookHandler implements WebhookHandlerInterface
{
    public function handle(TronAddress $address, TronTransaction $transaction): void
    {
        Log::error('NEW TRANSACTION FOR ADDRESS '.$address->id.' = '.$transaction->txid);
    }
}
```


## Requirements

The following versions of PHP are supported by this version.

* PHP 8.1 and older
* PHP Extensions: Decimal, GMP, BCMath, CType.
* Laravel Queues
