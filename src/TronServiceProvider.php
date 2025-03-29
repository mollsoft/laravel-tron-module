<?php

namespace Mollsoft\LaravelTronModule;

use Mollsoft\LaravelTronModule\Commands\NewTRC20Command;
use Mollsoft\LaravelTronModule\Commands\NewWalletCommand;
use Mollsoft\LaravelTronModule\Commands\NewAddressCommand;
use Mollsoft\LaravelTronModule\Commands\ImportAddressCommand;
use Mollsoft\LaravelTronModule\Commands\AddressSyncCommand;
use Mollsoft\LaravelTronModule\Commands\NewNodeCommand;
use Mollsoft\LaravelTronModule\Commands\NodeSyncCommand;
use Mollsoft\LaravelTronModule\Commands\SyncCommand;
use Mollsoft\LaravelTronModule\Commands\WalletSyncCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TronServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('tron')
            ->hasConfigFile()
            ->hasMigrations([
                'create_tron_nodes_table',
                'create_tron_wallets_table',
                'create_tron_trc20_table',
                'create_tron_addresses_table',
                'create_tron_transactions_table',
                'create_tron_deposits_table',
            ])
            ->runsMigrations()
            ->hasCommands(
                NewNodeCommand::class,
                NewWalletCommand::class,
                NewAddressCommand::class,
                ImportAddressCommand::class,
                NewTRC20Command::class,
                SyncCommand::class,
                AddressSyncCommand::class,
                WalletSyncCommand::class,
                NodeSyncCommand::class,
            )
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            });

        $this->app->singleton(Tron::class);
    }

    public function bootingPackage(): void
    {
        HasMany::macro('withParentWallet', function ($wallet) {
            return $this->afterRetrieving(function ($addresses) use ($wallet) {
                foreach ($addresses as $address) {
                    $address->setRelation('wallet', $wallet);
                }
            });
        });
    }
}
