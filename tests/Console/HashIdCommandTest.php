<?php

namespace Atldays\HashIds\Tests\Console;

use Atldays\HashIds\Tests\Fixtures\Models\TestUser;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserWithoutHashId;
use Atldays\HashIds\Tests\TestCase;

class HashIdCommandTest extends TestCase
{
    public function test_it_encodes_a_numeric_value_for_a_model_from_the_console(): void
    {
        $encoded = TestUser::encodeHashId(123);

        $this->artisan('hashid:encode', ['model' => TestUser::class, 'value' => '123'])
            ->expectsOutput($encoded)
            ->assertSuccessful();
    }

    public function test_it_rejects_an_invalid_numeric_value_for_console_encoding(): void
    {
        $this->artisan('hashid:encode', ['model' => TestUser::class, 'value' => 'abc'])
            ->expectsOutputToContain('The value must be a non-negative integer.')
            ->assertFailed();
    }

    public function test_it_rejects_a_model_without_hash_id_trait_for_console_encoding(): void
    {
        $this->artisan('hashid:encode', ['model' => TestUserWithoutHashId::class, 'value' => '123'])
            ->expectsOutputToContain('The model must be an Eloquent model class that uses the HasHashId trait.')
            ->assertFailed();
    }

    public function test_it_decodes_a_hash_id_for_a_model_from_the_console(): void
    {
        $encoded = TestUser::encodeHashId(123);

        $this->artisan('hashid:decode', ['model' => TestUser::class, 'value' => $encoded])
            ->expectsOutput('123')
            ->assertSuccessful();
    }

    public function test_it_rejects_an_invalid_hash_id_for_console_decoding(): void
    {
        $this->artisan('hashid:decode', ['model' => TestUser::class, 'value' => 'invalid-hash'])
            ->expectsOutputToContain('Unable to decode hash ID')
            ->assertFailed();
    }

    public function test_it_rejects_a_model_without_hash_id_trait_for_console_decoding(): void
    {
        $this->artisan('hashid:decode', ['model' => TestUserWithoutHashId::class, 'value' => 'invalid-hash'])
            ->expectsOutputToContain('The model must be an Eloquent model class that uses the HasHashId trait.')
            ->assertFailed();
    }
}
