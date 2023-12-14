<?php

namespace Mollsoft\LaravelTronModule;

use Mollsoft\LaravelTronModule\Api\Api;
use Mollsoft\LaravelTronModule\Api\HttpProvider;
use Mollsoft\LaravelTronModule\Commands\CreateNewTRC20Command;
use Mollsoft\LaravelTronModule\Commands\CreateNewWalletCommand;
use Mollsoft\LaravelTronModule\Commands\GenerateAddressCommand;
use Mollsoft\LaravelTronModule\Commands\ImportAddressCommand;
use Mollsoft\LaravelTronModule\Commands\TronSyncCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TronServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('tron')
            ->hasConfigFile()
            ->hasMigrations([
                'create_tron_wallets_table',
                'create_tron_trc20_table',
                'create_tron_addresses_table',
                'create_tron_transactions_table'
            ])
            ->runsMigrations()
            ->hasCommands(
                CreateNewWalletCommand::class,
                GenerateAddressCommand::class,
                ImportAddressCommand::class,
                CreateNewTRC20Command::class,
                TronSyncCommand::class,
            )
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            });

        $this->app->singleton(Api::class, function () {
            $fullNode = new HttpProvider(config('tron.full_node'), [
                'TRON-PRO-API-KEY' => config('tron.trongrid_api_key'),
            ]);
            $solidityNode = new HttpProvider(config('tron.solidity_node'), [
                'TRON-PRO-API-KEY' => config('tron.trongrid_api_key'),
            ]);
            return new Api($fullNode, $solidityNode);
        });

        $this->app->singleton(Tron::class);
    }
}
