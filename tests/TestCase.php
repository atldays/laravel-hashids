<?php

namespace Atldays\HashIds\Tests;

use Atldays\HashIds\HashIdServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('hashid.enable', false);
        $app['config']->set('hashid.strict', false);
        $app['config']->set('hashid.min_length', 12);
    }

    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            HashIdServiceProvider::class,
        ];
    }
}
