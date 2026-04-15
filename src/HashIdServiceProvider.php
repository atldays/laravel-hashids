<?php

namespace Atldays\HashIds;

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HashIdServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-hashids')
            ->hasConfigFile('hashid')
            ->hasTranslations();
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(HashIdVault::class);

        $this->app->bind(HashId::class, function (Application $app) {
            /** @var ConfigContract $config */
            $config = $app->make('config');

            return new HashId(
                minLength: $config->get('hashid.min_length'),
            );
        });
    }
}
