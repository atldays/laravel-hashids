<?php

namespace Atldays\HashIds\Tests\Concerns;

use Atldays\HashIds\Tests\Fixtures\Models\TestSerializedUser;
use Atldays\HashIds\Tests\Fixtures\Models\TestSerializedUserByPublicId;
use Atldays\HashIds\Tests\TestCase;

class SerializesHashIdTest extends TestCase
{
    public function test_it_replaces_the_default_source_column_with_hash_id_in_serialized_attributes(): void
    {
        config()->set('hashid.enabled', true);

        $user = TestSerializedUser::query()->create([
            'name' => 'Alice',
        ]);

        $attributes = $user->attributesToArray();

        $this->assertSame($user->hash_id, $attributes['id']);
        $this->assertSame('Alice', $attributes['name']);
        $this->assertIsString($attributes['id']);
    }

    public function test_it_replaces_a_custom_source_column_with_hash_id_in_serialized_attributes(): void
    {
        config()->set('hashid.enabled', true);

        $user = TestSerializedUserByPublicId::query()->create([
            'name' => 'Alice',
            'public_id' => 456789,
        ]);

        $attributes = $user->attributesToArray();

        $this->assertSame($user->getHashId(), $attributes['public_id']);
        $this->assertSame('Alice', $attributes['name']);
        $this->assertIsString($attributes['public_id']);
    }

    public function test_it_keeps_plain_source_values_in_serialization_when_hash_ids_are_disabled(): void
    {
        config()->set('hashid.enabled', false);

        $user = TestSerializedUser::query()->create([
            'name' => 'Alice',
        ]);

        $attributes = $user->attributesToArray();

        $this->assertSame($user->id, $attributes['id']);
        $this->assertIsInt($attributes['id']);
    }

    public function test_it_keeps_plain_custom_source_values_in_serialization_when_hash_ids_are_disabled(): void
    {
        config()->set('hashid.enabled', false);

        $user = TestSerializedUserByPublicId::query()->create([
            'name' => 'Alice',
            'public_id' => 456789,
        ]);

        $attributes = $user->attributesToArray();

        $this->assertSame(456789, $attributes['public_id']);
        $this->assertIsInt($attributes['public_id']);
    }
}
