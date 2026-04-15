<?php

namespace Atldays\HashIds\Tests\Concerns;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Atldays\HashIds\Exceptions\ModelNotFoundByHashIdException;
use Atldays\HashIds\Tests\Fixtures\Models\TestUser;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserByPublicId;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserWithRouteBinding;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserWithRouteBindingMethodOverride;
use Atldays\HashIds\Tests\TestCase;

class HasHashIdTest extends TestCase
{
    public function test_it_returns_hash_id_attribute_for_saved_model(): void
    {
        config()->set('hashid.enable', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $this->assertSame(TestUser::encodeHashId($user->id), $user->getHashId());
        $this->assertSame(TestUser::encodeHashId($user->id), $user->hash_id);
        $this->assertIsString($user->hash_id);
    }

    public function test_it_returns_null_hash_id_for_unsaved_model(): void
    {
        $user = new TestUser(['name' => 'Alice']);

        $this->assertNull($user->hash_id);
    }

    public function test_it_finds_model_by_hash_id(): void
    {
        config()->set('hashid.enable', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $found = TestUser::findByHashId($user->hash_id);

        $this->assertInstanceOf(TestUser::class, $found);
        $this->assertSame($user->id, $found->id);
    }

    public function test_it_returns_null_when_model_by_hash_id_is_missing(): void
    {
        config()->set('hashid.enable', true);

        $hashId = TestUser::encodeHashId(999);

        $this->assertNull(TestUser::findByHashId($hashId));
    }

    public function test_it_throws_for_invalid_hash_id(): void
    {
        config()->set('hashid.enable', true);
        config()->set('hashid.strict', true);

        $this->expectException(InvalidHashIdException::class);

        TestUser::findByHashId('invalid-hash');
    }

    public function test_it_throws_model_not_found_exception_for_missing_model(): void
    {
        config()->set('hashid.enable', true);

        $hashId = TestUser::encodeHashId(999);

        try {
            TestUser::findOrFailByHashId($hashId);
            $this->fail('Expected model not found exception was not thrown.');
        } catch (ModelNotFoundByHashIdException $exception) {
            $this->assertSame(TestUser::class, $exception->getModel());
            $this->assertSame([999], $exception->getIds());
            $this->assertStringContainsString((string) $hashId, $exception->getMessage());
        }
    }

    public function test_it_allows_plain_integer_values_when_not_strict(): void
    {
        $user = TestUser::query()->create(['name' => 'Alice']);

        $found = TestUser::findByHashId($user->id);

        $this->assertInstanceOf(TestUser::class, $found);
        $this->assertSame($user->id, $found->id);
    }

    public function test_it_can_use_custom_numeric_column_for_hash_id(): void
    {
        config()->set('hashid.enable', true);

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

    public function test_it_can_resolve_route_binding_by_plain_id_when_not_strict(): void
    {
        config()->set('hashid.enable', true);
        config()->set('hashid.strict', false);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $resolved = (new TestUserWithRouteBinding)->resolveRouteBinding((string) $user->id);

        $this->assertInstanceOf(TestUserWithRouteBinding::class, $resolved);
        $this->assertSame($user->id, $resolved->id);
    }

    public function test_it_can_resolve_route_binding_by_hash_id(): void
    {
        config()->set('hashid.enable', true);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $resolved = (new TestUserWithRouteBinding)->resolveRouteBinding($user->hash_id);

        $this->assertInstanceOf(TestUserWithRouteBinding::class, $resolved);
        $this->assertSame($user->id, $resolved->id);
    }

    public function test_it_uses_only_hash_id_route_binding_in_strict_mode(): void
    {
        config()->set('hashid.enable', true);
        config()->set('hashid.strict', true);

        $user = TestUserWithRouteBinding::query()->create(['name' => 'Alice']);

        $this->expectException(InvalidHashIdException::class);

        (new TestUserWithRouteBinding)->resolveRouteBinding((string) $user->id);
    }

    public function test_it_allows_overriding_property_based_route_binding_with_method(): void
    {
        config()->set('hashid.enable', true);
        config()->set('hashid.strict', false);

        $user = TestUserWithRouteBindingMethodOverride::query()->create(['name' => 'Alice']);

        $resolved = (new TestUserWithRouteBindingMethodOverride)->resolveRouteBinding((string) $user->id);

        $this->assertInstanceOf(TestUserWithRouteBindingMethodOverride::class, $resolved);
        $this->assertSame($user->id, $resolved->id);
    }
}
