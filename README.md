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

## Requirements

The following versions of PHP are supported by this version.

* PHP 8.2 and older
* PHP Extensions: Decimal, GMP, BCMath, CType.
* Laravel Queues


## Installation
You can install the package via composer:
```bash
composer require mollsoft/laravel-tron-module
```

After you can run installer using command:
```bash
php artisan tron:install
```

And run migrations:
```bash
php artisan migrate
```

Register Service Provider and Facade in app, edit `config/app.php`:
```php
'providers' => ServiceProvider::defaultProviders()->merge([
    ...,
    \Mollsoft\LaravelTronModule\TronServiceProvider::class,
])->toArray(),

'aliases' => Facade::defaultAliases()->merge([
    ...,
    'Tron' => \Mollsoft\LaravelTronModule\Facades\Tron::class,
])->toArray(),
```

In file `app/Console/Kernel` in method `schedule(Schedule $schedule)` add
```
$schedule->command('tron:sync')
    ->everyMinute()
    ->runInBackground();
```

## Commands

Synchronizing everything
```bash
php artisan tron:sync
```

Node synchronization
```bash
php artisan tron:sync-node NODE_ID
```

Wallet synchronization
```bash
php artisan tron:sync-wallet WALLET_ID
```

Address synchronization
```bash
php artisan tron:sync-address ADDRESS_ID
```

Create Tron Node. Before you need register account in https://trongrid.io and generate API Key.
```bash
php artisan tron:new-node
```

Create Tron Wallet.
```bash
php artisan tron:new-wallet
```

Generate Tron Address.
```bash
php artisan tron:new-address
```

Import watch only address.
```bash
php artisan tron:import-address
```

Create TRC-20 Token
```bash
php artisan tron:new-trc20
```


