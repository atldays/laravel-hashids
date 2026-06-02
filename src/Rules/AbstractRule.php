<?php

namespace Atldays\HashIds\Rules;

use Atldays\HashIds\Concerns\HasHashId;
use Atldays\HashIds\Contracts\HasHashIdModel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

abstract class AbstractRule implements ValidationRule
{
    /**
     * @var class-string<Model&HasHashIdModel>
     */
    protected readonly string $model;

    /**
     * Get the translation key for a single invalid value.
     */
    abstract protected function singleValidationMessageKey(): string;

    /**
     * Get the translation key for multiple invalid values.
     */
    abstract protected function multipleValidationMessageKey(): string;

    /**
     * Determine whether the given value passes this rule.
     */
    abstract protected function passesValue(mixed $value): bool;

    /**
     * @param class-string<Model> $model
     */
    public function __construct(
        string $model,
    ) {
        $this->assertHashIdModel($model);

        $this->model = $model;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->isSkippableValue($value)) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (!$this->passesValue($item)) {
                    $fail($this->multipleValidationMessageKey())->translate();

                    return;
                }
            }

            return;
        }

        if (!$this->passesValue($value)) {
            $fail($this->singleValidationMessageKey())->translate();
        }
    }

    protected function isEnabled(): bool
    {
        return (bool)Config::get('hashid.enabled', true);
    }

    protected function isSkippableValue(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    protected function isPlainValue(mixed $value): bool
    {
        return is_int($value) || (is_string($value) && ctype_digit($value));
    }

    /**
     * @param class-string<Model> $model
     *
     * @phpstan-assert class-string<Model&HasHashIdModel> $model
     */
    protected function assertHashIdModel(string $model): void
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new InvalidArgumentException(sprintf('%s expects an Eloquent model class, `%s` given.', static::class, $model));
        }

        if (
            !is_subclass_of($model, HasHashIdModel::class)
            && !in_array(HasHashId::class, class_uses_recursive($model), true)
        ) {
            throw new InvalidArgumentException(sprintf('Model `%s` must use the `%s` trait or implement the `%s` contract to be validated by `%s`.', $model, HasHashId::class, HasHashIdModel::class, static::class));
        }
    }
}
