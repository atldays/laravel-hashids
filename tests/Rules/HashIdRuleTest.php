<?php

namespace Atldays\HashIds\Tests\Rules;

use Atldays\HashIds\Rules\HashId;
use Atldays\HashIds\Rules\HashIdExists;
use Atldays\HashIds\Tests\Fixtures\Models\TestUser;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserByPublicId;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserWithoutHashId;
use Atldays\HashIds\Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class HashIdRuleTest extends TestCase
{
    public function test_it_validates_single_hash_id(): void
    {
        config()->set('hashid.enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $validator = Validator::make(
            ['user' => $user->hash_id],
            ['user' => [new HashId(TestUser::class)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_invalid_single_hash_id(): void
    {
        config()->set('hashid.enabled', true);

        $validator = Validator::make(
            ['user' => 'invalid-hash'],
            ['user' => [new HashId(TestUser::class)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            [__('laravel-hashids::validation.hash_id', ['attribute' => 'user'])],
            $validator->errors()->get('user'),
        );
    }

    public function test_it_validates_array_of_hash_ids(): void
    {
        config()->set('hashid.enabled', true);

        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);

        $validator = Validator::make(
            ['users' => [$firstUser->hash_id, $secondUser->hash_id]],
            ['users' => [new HashId(TestUser::class)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_array_with_invalid_hash_id(): void
    {
        config()->set('hashid.enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $validator = Validator::make(
            ['users' => [$user->hash_id, 'invalid-hash']],
            ['users' => [new HashId(TestUser::class)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            [__('laravel-hashids::validation.hash_ids', ['attribute' => 'users'])],
            $validator->errors()->get('users'),
        );
    }

    public function test_it_allows_numeric_values_when_http_hash_ids_are_disabled(): void
    {
        config()->set('hashid.enabled', false);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $validator = Validator::make(
            ['user' => (string)$user->id],
            ['user' => [new HashId(TestUser::class)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_numeric_values_when_http_hash_ids_are_enabled(): void
    {
        config()->set('hashid.enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $validator = Validator::make(
            ['user' => (string)$user->id],
            ['user' => [new HashId(TestUser::class)]],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_it_allows_nullable_like_values(): void
    {
        $validator = Validator::make(
            ['user' => null, 'users' => ['']],
            [
                'user' => [new HashId(TestUser::class)],
                'users' => [new HashIdExists(TestUser::class)],
            ],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_allows_null_when_rule_is_used_with_nullable(): void
    {
        $validator = Validator::make(
            ['user' => null, 'existing_user' => null],
            [
                'user' => ['nullable', new HashId(TestUser::class)],
                'existing_user' => ['nullable', new HashIdExists(TestUser::class)],
            ],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_null_when_rule_is_used_with_required(): void
    {
        $validator = Validator::make(
            ['user' => null, 'existing_user' => null],
            [
                'user' => ['required', new HashId(TestUser::class)],
                'existing_user' => ['required', new HashIdExists(TestUser::class)],
            ],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(['The user field is required.'], $validator->errors()->get('user'));
        $this->assertSame(['The existing user field is required.'], $validator->errors()->get('existing_user'));
    }

    public function test_it_rejects_missing_value_when_rule_is_used_with_required(): void
    {
        $validator = Validator::make(
            [],
            [
                'user' => ['required', new HashId(TestUser::class)],
                'existing_user' => ['required', new HashIdExists(TestUser::class)],
            ],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(['The user field is required.'], $validator->errors()->get('user'));
        $this->assertSame(['The existing user field is required.'], $validator->errors()->get('existing_user'));
    }

    public function test_it_requires_hash_id_capabilities_for_model(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must use');

        new HashId(TestUserWithoutHashId::class);
    }

    public function test_it_validates_existing_single_hash_id(): void
    {
        config()->set('hashid.enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $validator = Validator::make(
            ['user' => $user->hash_id],
            ['user' => [new HashIdExists(TestUser::class)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_validates_existing_numeric_value_by_custom_hash_id_column_when_http_hash_ids_are_disabled(): void
    {
        config()->set('hashid.enabled', false);

        TestUserByPublicId::query()->create([
            'name' => 'Alice',
            'public_id' => 456789,
        ]);

        $validator = Validator::make(
            ['user' => '456789'],
            ['user' => [new HashIdExists(TestUserByPublicId::class)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_missing_single_hash_id(): void
    {
        config()->set('hashid.enabled', true);

        $validator = Validator::make(
            ['user' => TestUser::encodeHashId(999)],
            ['user' => [new HashIdExists(TestUser::class)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            [__('laravel-hashids::validation.hash_id_exists', ['attribute' => 'user'])],
            $validator->errors()->get('user'),
        );
    }

    public function test_it_validates_array_of_existing_hash_ids(): void
    {
        config()->set('hashid.enabled', true);

        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);

        $validator = Validator::make(
            ['users' => [$firstUser->hash_id, $secondUser->hash_id]],
            ['users' => [new HashIdExists(TestUser::class)]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_it_rejects_array_with_missing_hash_id(): void
    {
        config()->set('hashid.enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $validator = Validator::make(
            ['users' => [$user->hash_id, TestUser::encodeHashId(999)]],
            ['users' => [new HashIdExists(TestUser::class)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            [__('laravel-hashids::validation.hash_ids_exist', ['attribute' => 'users'])],
            $validator->errors()->get('users'),
        );
    }
}
