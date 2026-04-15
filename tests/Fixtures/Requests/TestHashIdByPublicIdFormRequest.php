<?php

namespace Atldays\HashIds\Tests\Fixtures\Requests;

use Atldays\HashIds\Http\Attributes\HashIdField;
use Atldays\HashIds\Http\Concerns\InteractsWithHashIds;
use Atldays\HashIds\Tests\Fixtures\Models\TestUserByPublicId;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;

#[HashIdField('users', TestUserByPublicId::class)]
class TestHashIdByPublicIdFormRequest extends FormRequest
{
    use InteractsWithHashIds;

    /**
     * @var array<string, class-string<TestUserByPublicId>>
     */
    protected array $hashIdFields = [
        'author' => TestUserByPublicId::class,
    ];

    public function rules(): array
    {
        return [];
    }

    public function normalizeHashIds(): void
    {
        $this->passedValidation();
    }

    public function resolveHashedModel(string $field): ?TestUserByPublicId
    {
        /** @var TestUserByPublicId|null $model */
        $model = $this->hashedModel($field);

        return $model;
    }

    /**
     * @return Collection<int, TestUserByPublicId>
     */
    public function resolveHashedModels(string $field): Collection
    {
        /** @var Collection<int, TestUserByPublicId> $models */
        $models = $this->hashedModels($field);

        return $models;
    }
}
