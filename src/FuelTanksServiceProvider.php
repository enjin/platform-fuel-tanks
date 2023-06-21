<?php

namespace Enjin\Platform\FuelTanks;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FuelTanksServiceProvider extends PackageServiceProvider
{
    /**
     * Configure provider.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('platform-fuel-tanks')
            ->hasConfigFile(['enjin-platform-fuel-tanks'])
            ->hasMigrations(
                'create_fuel_tanks_table',
                'create_fuel_tank_accounts_table',
                'create_fuel_tank_rules_table'
            )
            ->hasTranslations();
    }

    /**
     * Register provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Boot provider.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function packageRegistered()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'enjin-platform-fuel-tanks');
    }
}
