<?php

namespace Atldays\HashIds\Tests;

use Atldays\HashIds\HashIdServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('test_users');
        Schema::create('test_users', function ($table) {
            $table->id();
            $table->unsignedBigInteger('public_id')->nullable();
            $table->string('name');
        });
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('hashid.http_enabled', false);
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
