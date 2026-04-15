<?php

namespace Atldays\HashIds\Tests\Concerns;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Atldays\HashIds\Exceptions\ModelNotFoundByHashIdException;
use Atldays\HashIds\Tests\Fixtures\Models\TestUser;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserByPublicId;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserWithRouteBinding;
use Atldays\HashIds\Tests\TestCase;

class HasHashIdTest extends TestCase
{
    public function test_it_returns_hash_id_attribute_for_saved_model(): void
    {
        $user = TestUser::query()->create(['name' => 'Alice']);

        $this->assertSame(TestUser::encodeHashId($user->id), $user->getHashId());
        $this->assertSame(TestUser::encodeHashId($user->id), $user->hash_id);
        $this->assertIsString($user->hash_id);
    }

    public function test_it_returns_null_hash_id_for_unsaved_model(): void
    {
        $user = new TestUser(['name' => 'Alice']);

        $this->assertNull($user->getHashId());
        $this->assertNull($user->hash_id);
    }

    public function test_it_finds_model_by_hash_id(): void
    {
        $user = TestUser::query()->create(['name' => 'Alice']);

        $found = TestUser::findByHashId($user->hash_id);

        $this->assertInstanceOf(TestUser::class, $found);
        $this->assertSame($user->id, $found->id);
    }

    public function test_it_returns_null_when_model_by_hash_id_is_missing(): void
    {
        $hashId = TestUser::encodeHashId(999);

        $this->assertNull(TestUser::findByHashId($hashId));
    }

    public function test_it_can_find_many_models_by_hash_ids(): void
    {
        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);
        TestUser::query()->create(['name' => 'Charlie']);

        $results = TestUser::findManyByHashId([$firstUser->hash_id, $secondUser->hash_id]);

        $this->assertCount(2, $results);
        $this->assertEqualsCanonicalizing([$firstUser->id, $secondUser->id], $results->pluck('id')->all());
    }

    public function test_it_rejects_empty_hash_id_string(): void
    {
        $this->expectException(InvalidHashIdException::class);

        TestUser::findByHashId('');
    }

    public function test_it_throws_for_invalid_hash_id(): void
    {
        $this->expectException(InvalidHashIdException::class);

        TestUser::findByHashId('invalid-hash');
    }

    public function test_it_throws_model_not_found_exception_for_missing_model(): void
    {
        config()->set('hashid.http_enabled', true);

        $hashId = TestUser::encodeHashId(999);

        try {
            TestUser::findOrFailByHashId($hashId);
            $this->fail('Expected model not found exception was not thrown.');
        } catch (ModelNotFoundByHashIdException $exception) {
            $this->assertSame(TestUser::class, $exception->getModel());
            $this->assertSame([999], $exception->getIds());
            $this->assertStringContainsString((string)$hashId, $exception->getMessage());
        }
    }

    public function test_it_rejects_plain_integer_values_for_hash_id_lookups(): void
    {
        $this->expectException(InvalidHashIdException::class);

        TestUser::findByHashId(1);
    }

    public function test_it_rejects_null_values_for_hash_id_lookups(): void
    {
        $this->expectException(\TypeError::class);

        TestUser::findByHashId(null);
    }

    public function test_it_can_find_model_or_execute_callback_by_hash_id(): void
    {
        $user = TestUser::query()->create(['name' => 'Alice']);

        $found = TestUser::findOrByHashId($user->hash_id, fn () => 'fallback');
        $missing = TestUser::findOrByHashId(TestUser::encodeHashId(999), fn () => 'fallback');

        $this->assertInstanceOf(TestUser::class, $found);
        $this->assertSame('fallback', $missing);
    }

    public function test_it_can_find_model_or_return_new_instance_by_hash_id(): void
    {
        config()->set('hashid.http_enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $found = TestUser::findOrNewByHashId($user->hash_id);
        $missing = TestUser::findOrNewByHashId(TestUser::encodeHashId(999), ['name' => 'Fallback']);

        $this->assertInstanceOf(TestUser::class, $found);
        $this->assertTrue($found->exists);
        $this->assertInstanceOf(TestUser::class, $missing);
        $this->assertFalse($missing->exists);
        $this->assertSame('Fallback', $missing->name);
    }

    public function test_it_can_scope_query_by_hash_id(): void
    {
        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        TestUser::query()->create(['name' => 'Bob']);

        $found = TestUser::query()
            ->whereHashId($firstUser->hash_id)
            ->first();

        $this->assertInstanceOf(TestUser::class, $found);
        $this->assertSame($firstUser->id, $found->id);
    }

    public function test_it_rejects_null_when_scope_hash_id_value_is_invalid(): void
    {
        $this->expectException(\TypeError::class);

        TestUser::query()->whereHashId(null);
    }

    public function test_it_can_scope_query_by_multiple_hash_ids(): void
    {
        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);
        TestUser::query()->create(['name' => 'Charlie']);

        $results = TestUser::query()
            ->whereHashIds([$firstUser->hash_id, $secondUser->hash_id])
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame([$firstUser->id, $secondUser->id], $results->pluck('id')->all());
    }

    public function test_it_can_exclude_single_hash_id_from_query(): void
    {
        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);

        $results = TestUser::query()
            ->whereHashIdNot($firstUser->hash_id)
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame($secondUser->id, $results->sole()->id);
    }

    public function test_it_rejects_null_when_excluding_hash_id_with_invalid_value(): void
    {
        $this->expectException(\TypeError::class);

        TestUser::query()->whereHashIdNot(null);
    }

    public function test_it_rejects_invalid_values_when_scope_hash_ids_are_invalid(): void
    {
        $this->expectException(InvalidHashIdException::class);

        TestUser::query()->whereHashIds(['']);
    }

    public function test_it_can_exclude_multiple_hash_ids_from_query(): void
    {
        config()->set('hashid.http_enabled', true);

        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);
        $thirdUser = TestUser::query()->create(['name' => 'Charlie']);

        $results = TestUser::query()
            ->whereHashIdsNot([$firstUser->hash_id, $secondUser->hash_id])
            ->get();

        $this->assertCount(1, $results);
        $this->assertSame($thirdUser->id, $results->sole()->id);
    }

    public function test_it_rejects_invalid_values_when_excluding_hash_ids_are_invalid(): void
    {
        $this->expectException(InvalidHashIdException::class);

        TestUser::query()->whereHashIdsNot(['']);
    }

    public function test_it_can_use_custom_numeric_column_for_hash_id(): void
    {
        $user = TestUserByPublicId::query()->create([
            'name' => 'Alice',
            'public_id' => 456789,
        ]);

        $this->assertSame(
            TestUserByPublicId::encodeHashId(456789),
            $user->getHashId(),
        );

        $found = TestUserByPublicId::findByHashId($user->getHashId());

        $this->assertInstanceOf(TestUserByPublicId::class, $found);
        $this->assertSame($user->id, $found->id);
        $this->assertSame(456789, $found->public_id);
    }

    public function test_it_can_find_model_by_decoded_hash_id_value(): void
    {
        $user = TestUserByPublicId::query()->create([
            'name' => 'Alice',
            'public_id' => 456789,
        ]);

        $found = TestUserByPublicId::findByHashIdValue(456789);

        $this->assertInstanceOf(TestUserByPublicId::class, $found);
        $this->assertSame($user->id, $found->id);
        $this->assertSame(456789, $found->public_id);
    }

    public function test_it_can_resolve_route_binding_by_plain_id_when_http_hash_ids_are_disabled(): void
    {
        config()->set('hashid.http_enabled', false);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $resolved = (new TestUserWithRouteBinding)->resolveRouteBinding((string)$user->id);

        $this->assertInstanceOf(TestUserWithRouteBinding::class, $resolved);
        $this->assertSame($user->id, $resolved->id);
    }

    public function test_it_can_resolve_route_binding_by_hash_id(): void
    {
        config()->set('hashid.http_enabled', true);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $resolved = (new TestUserWithRouteBinding)->resolveRouteBinding($user->hash_id);

        $this->assertInstanceOf(TestUserWithRouteBinding::class, $resolved);
        $this->assertSame($user->id, $resolved->id);
    }

    public function test_it_returns_hash_id_as_route_key_when_routing_trait_is_used(): void
    {
        config()->set('hashid.http_enabled', true);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $this->assertSame($user->getHashId(), $user->getRouteKey());
    }

    public function test_it_returns_plain_id_as_route_key_when_http_hash_ids_are_disabled(): void
    {
        config()->set('hashid.http_enabled', false);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $this->assertSame($user->getKey(), $user->getRouteKey());
    }

    public function test_it_returns_null_for_empty_route_binding_value(): void
    {
        config()->set('hashid.http_enabled', true);

        $resolved = (new TestUserWithRouteBinding)->resolveRouteBinding('');

        $this->assertNull($resolved);
    }

    public function test_it_rejects_plain_id_route_binding_when_http_hash_ids_are_enabled(): void
    {
        config()->set('hashid.http_enabled', true);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $this->expectException(InvalidHashIdException::class);

        (new TestUserWithRouteBinding)->resolveRouteBinding((string)$user->id);
    }
}
