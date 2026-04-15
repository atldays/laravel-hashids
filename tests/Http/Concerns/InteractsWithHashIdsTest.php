<?php

namespace Atldays\HashIds\Tests\Http\Concerns;

use Atldays\HashIds\Tests\Fixtures\Models\TestUser;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserByPublicId;
use Atldays\HashIds\Tests\Fixtures\Requests\InheritedHashIdFormRequest;
use Atldays\HashIds\Tests\Fixtures\Requests\TestHashIdByPublicIdFormRequest;
use Atldays\HashIds\Tests\Fixtures\Requests\TestHashIdFormRequest;
use Atldays\HashIds\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class InteractsWithHashIdsTest extends TestCase
{
    public function test_it_collects_hash_id_fields_from_property_and_attributes(): void
    {
        $request = $this->makeRequest();

        $this->assertSame([
            'author' => TestUser::class,
            'filters.author' => TestUser::class,
            'users' => TestUser::class,
            'filters.users' => TestUser::class,
        ], $request->resolvedHashIdFields());
    }

    public function test_it_collects_hash_id_fields_from_parent_request_attributes(): void
    {
        $request = $this->makeInheritedRequest();

        $this->assertSame([
            'author' => TestUser::class,
            'filters.author' => TestUser::class,
            'users' => TestUser::class,
            'filters.users' => TestUser::class,
            'inherited.users' => TestUser::class,
        ], $request->resolvedHashIdFields());
    }

    public function test_it_decodes_single_and_array_hash_id_fields_after_validation(): void
    {
        config()->set('hashid.http_enabled', true);

        $author = TestUser::query()->create(['name' => 'Alice']);
        $firstUser = TestUser::query()->create(['name' => 'Bob']);
        $secondUser = TestUser::query()->create(['name' => 'Charlie']);

        $request = $this->makeRequest([
            'author' => $author->hash_id,
            'users' => [$firstUser->hash_id, $secondUser->hash_id],
        ]);

        $request->normalizeHashIds();

        $this->assertSame($author->id, $request->input('author'));
        $this->assertSame([$firstUser->id, $secondUser->id], $request->input('users'));
    }

    public function test_it_keeps_nullable_like_values_unchanged_when_decoding_hash_id_fields(): void
    {
        $request = $this->makeRequest([
            'author' => null,
            'users' => [''],
        ]);

        $request->normalizeHashIds();

        $this->assertNull($request->input('author'));
        $this->assertSame([''], $request->input('users'));
    }

    public function test_it_can_resolve_single_model_from_decoded_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $author = TestUser::query()->create(['name' => 'Alice']);

        $request = $this->makeRequest([
            'author' => $author->hash_id,
        ]);

        $request->normalizeHashIds();

        $model = $request->resolveHashedModel('author');

        $this->assertInstanceOf(TestUser::class, $model);
        $this->assertSame($author->id, $model->id);
    }

    public function test_it_returns_null_when_single_model_hash_id_field_is_empty(): void
    {
        $request = $this->makeRequest([
            'author' => null,
        ]);

        $request->normalizeHashIds();

        $this->assertNull($request->resolveHashedModel('author'));
    }

    public function test_it_can_resolve_single_model_from_plain_value_when_http_hash_ids_are_disabled(): void
    {
        config()->set('hashid.http_enabled', false);

        $author = TestUserByPublicId::query()->create([
            'name' => 'Alice',
            'public_id' => 456789,
        ]);

        $request = $this->makePublicIdRequest([
            'author' => 456789,
        ]);

        $request->normalizeHashIds();

        $model = $request->resolveHashedModel('author');

        $this->assertInstanceOf(TestUserByPublicId::class, $model);
        $this->assertSame($author->id, $model->id);
        $this->assertSame(456789, $model->public_id);
    }

    public function test_it_can_resolve_single_model_or_fail_from_decoded_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $author = TestUser::query()->create(['name' => 'Alice']);

        $request = $this->makeRequest([
            'author' => $author->hash_id,
        ]);

        $request->normalizeHashIds();

        $model = $request->resolveHashedModelOrFail('author');

        $this->assertInstanceOf(TestUser::class, $model);
        $this->assertSame($author->id, $model->id);
    }

    public function test_it_throws_when_single_model_or_fail_hash_id_field_is_empty(): void
    {
        $request = $this->makeRequest([
            'author' => null,
        ]);

        $request->normalizeHashIds();

        $this->expectException(ModelNotFoundException::class);

        $request->resolveHashedModelOrFail('author');
    }

    public function test_it_can_resolve_model_collection_from_decoded_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);

        $request = $this->makeRequest([
            'users' => [$firstUser->hash_id, $secondUser->hash_id],
        ]);

        $request->normalizeHashIds();

        $models = $request->resolveHashedModels('users');

        $this->assertCount(2, $models);
        $this->assertSame([$firstUser->id, $secondUser->id], $models->pluck('id')->all());
    }

    public function test_it_returns_empty_collection_when_model_collection_hash_id_field_is_empty(): void
    {
        $request = $this->makeRequest([
            'users' => [''],
        ]);

        $request->normalizeHashIds();

        $models = $request->resolveHashedModels('users');

        $this->assertCount(0, $models);
    }

    public function test_it_can_resolve_model_collection_from_plain_values_when_http_hash_ids_are_disabled(): void
    {
        config()->set('hashid.http_enabled', false);

        $firstUser = TestUserByPublicId::query()->create([
            'name' => 'Alice',
            'public_id' => 456789,
        ]);
        $secondUser = TestUserByPublicId::query()->create([
            'name' => 'Bob',
            'public_id' => 567890,
        ]);

        $request = $this->makePublicIdRequest([
            'users' => [456789, 567890],
        ]);

        $request->normalizeHashIds();

        $models = $request->resolveHashedModels('users');

        $this->assertCount(2, $models);
        $this->assertSame([$firstUser->id, $secondUser->id], $models->pluck('id')->all());
        $this->assertSame([456789, 567890], $models->pluck('public_id')->all());
    }

    public function test_it_decodes_hash_id_fields_using_dot_notation(): void
    {
        config()->set('hashid.http_enabled', true);

        $author = TestUser::query()->create(['name' => 'Alice']);
        $firstUser = TestUser::query()->create(['name' => 'Bob']);
        $secondUser = TestUser::query()->create(['name' => 'Charlie']);

        $request = $this->makeRequest([
            'filters' => [
                'author' => $author->hash_id,
                'users' => [$firstUser->hash_id, $secondUser->hash_id],
            ],
        ]);

        $request->normalizeHashIds();

        $this->assertSame($author->id, $request->input('filters.author'));
        $this->assertSame([$firstUser->id, $secondUser->id], $request->input('filters.users'));
    }

    public function test_it_decodes_inherited_hash_id_field_attributes(): void
    {
        config()->set('hashid.http_enabled', true);

        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);

        $request = $this->makeInheritedRequest([
            'inherited' => [
                'users' => [$firstUser->hash_id, $secondUser->hash_id],
            ],
        ]);

        $request->normalizeHashIds();

        $this->assertSame([$firstUser->id, $secondUser->id], $request->input('inherited.users'));
    }

    public function test_it_can_resolve_single_model_from_dot_notation_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $author = TestUser::query()->create(['name' => 'Alice']);

        $request = $this->makeRequest([
            'filters' => [
                'author' => $author->hash_id,
            ],
        ]);

        $request->normalizeHashIds();

        $model = $request->resolveHashedModel('filters.author');

        $this->assertInstanceOf(TestUser::class, $model);
        $this->assertSame($author->id, $model->id);
    }

    public function test_it_can_resolve_model_collection_from_dot_notation_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $firstUser = TestUser::query()->create(['name' => 'Alice']);
        $secondUser = TestUser::query()->create(['name' => 'Bob']);

        $request = $this->makeRequest([
            'filters' => [
                'users' => [$firstUser->hash_id, $secondUser->hash_id],
            ],
        ]);

        $request->normalizeHashIds();

        $models = $request->resolveHashedModels('filters.users');

        $this->assertCount(2, $models);
        $this->assertSame([$firstUser->id, $secondUser->id], $models->pluck('id')->all());
    }

    public function test_it_throws_when_resolving_unconfigured_hash_id_field(): void
    {
        $request = $this->makeRequest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not configured');

        $request->resolveHashedModel('unknown');
    }

    public function test_it_throws_when_resolving_single_model_from_array_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $request = $this->makeRequest([
            'users' => [$user->hash_id],
        ]);

        $request->normalizeHashIds();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be decoded to an integer');

        $request->resolveHashedModel('users');
    }

    public function test_it_throws_when_resolving_single_model_or_fail_from_array_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $request = $this->makeRequest([
            'users' => [$user->hash_id],
        ]);

        $request->normalizeHashIds();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be decoded to an integer');

        $request->resolveHashedModelOrFail('users');
    }

    public function test_it_throws_when_single_model_or_fail_cannot_find_model(): void
    {
        $request = $this->makeRequest([
            'author' => 999,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $request->resolveHashedModelOrFail('author');
    }

    public function test_it_throws_when_resolving_model_collection_from_scalar_hash_id_field(): void
    {
        config()->set('hashid.http_enabled', true);

        $user = TestUser::query()->create(['name' => 'Alice']);

        $request = $this->makeRequest([
            'author' => $user->hash_id,
        ]);

        $request->normalizeHashIds();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be decoded to an array');

        $request->resolveHashedModels('author');
    }

    private function makeRequest(array $input = []): TestHashIdFormRequest
    {
        $baseRequest = Request::create('/', 'GET', $input);

        /** @var TestHashIdFormRequest $request */
        $request = TestHashIdFormRequest::createFromBase($baseRequest);

        return $request;
    }

    private function makeInheritedRequest(array $input = []): InheritedHashIdFormRequest
    {
        $baseRequest = Request::create('/', 'GET', $input);

        /** @var InheritedHashIdFormRequest $request */
        $request = InheritedHashIdFormRequest::createFromBase($baseRequest);

        return $request;
    }

    private function makePublicIdRequest(array $input = []): TestHashIdByPublicIdFormRequest
    {
        $baseRequest = Request::create('/', 'GET', $input);

        /** @var TestHashIdByPublicIdFormRequest $request */
        $request = TestHashIdByPublicIdFormRequest::createFromBase($baseRequest);

        return $request;
    }
}
