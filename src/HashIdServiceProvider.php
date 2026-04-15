<?php

namespace Atldays\HashIds;

use Atldays\HashIds\Console\Commands\DecodeCommand;
use Atldays\HashIds\Console\Commands\EncodeCommand;
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
            ->hasTranslations()
            ->hasCommands([
                EncodeCommand::class,
                DecodeCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(HashIdVault::class);

        $this->app->bind(HashId::class, function (Application $app, array $parameters) {
            /** @var ConfigContract $config */
            $config = $app->make('config');

            return new HashId(
                salt: (string)($parameters['salt'] ?? $config->get('hashid.salt')),
                length: (int)($parameters['length'] ?? $config->get('hashid.length')),
                alphabet: (string)($parameters['alphabet'] ?? $config->get('hashid.alphabet')),
            );
        });
    }
}
