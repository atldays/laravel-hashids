<?php

namespace Atldays\HashIds\Tests\Fixtures\Requests;

use Atldays\HashIds\Http\Attributes\HashIdField;
use Atldays\HashIds\Http\Concerns\InteractsWithHashIds;
use Atldays\HashIds\Tests\Fixtures\Models\TestUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;

#[HashIdField('users', TestUser::class)]
#[HashIdField('filters.users', TestUser::class)]
#[HashIdField('items.*.users', TestUser::class)]
class TestHashIdFormRequest extends FormRequest
{
    use InteractsWithHashIds;

    /**
     * @var array<string, class-string<TestUser>>
     */
    protected array $hashIdFields = [
        'author' => TestUser::class,
        'filters.author' => TestUser::class,
        'items.*.author' => TestUser::class,
    ];

    public function rules(): array
    {
        return [];
    }

    /**
     * @return array<string, class-string<TestUser>>
     */
    public function resolvedHashIdFields(): array
    {
        return $this->getHashIdFields();
    }

    public function normalizeHashIds(): void
    {
        $this->passedValidation();
    }

    public function resolveHashedModel(string $field): ?TestUser
    {
        /** @var TestUser|null $model */
        $model = $this->hashedModel($field);

        return $model;
    }

    public function resolveHashedModelOrFail(string $field): TestUser
    {
        /** @var TestUser $model */
        $model = $this->hashedModelOrFail($field);

        return $model;
    }

    /**
     * @return Collection<int, TestUser>
     */
    public function resolveHashedModels(string $field): Collection
    {
        /** @var Collection<int, TestUser> $models */
        $models = $this->hashedModels($field);

        return $models;
    }
}
