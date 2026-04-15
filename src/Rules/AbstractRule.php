<?php

namespace Atldays\HashIds\Rules;

use Atldays\HashIds\Concerns\HasHashId;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

abstract class AbstractRule implements ValidationRule
{
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
        protected readonly string $model,
    ) {
        if (!is_subclass_of($this->model, Model::class)) {
            throw new InvalidArgumentException(sprintf('%s expects an Eloquent model class, `%s` given.', static::class, $this->model));
        }

        if (!in_array(HasHashId::class, class_uses_recursive($this->model), true)) {
            throw new InvalidArgumentException(sprintf('Model `%s` must use the `%s` trait to be validated by `%s`.', $this->model, HasHashId::class, static::class));
        }
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
}
