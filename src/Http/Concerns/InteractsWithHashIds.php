<?php

namespace Atldays\HashIds\Http\Concerns;

use Atldays\HashIds\Http\Attributes\HashIdField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use ReflectionClass;

trait InteractsWithHashIds
{
    /**
     * Resolve a configured hash ID field to its corresponding model instance.
     */
    public function hashedModel(string $field): ?Model
    {
        $model = $this->getHashIdFieldModel($field);
        $value = $this->input($field);

        if ($value === null || $value === '') {
            return null;
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf('Hash ID field `%s` must be decoded to an integer before resolving a model.', $field));
        }

        return $model::findByHashIdValue($value);
    }

    /**
     * Resolve a configured hash ID field to its corresponding model instance or fail.
     */
    public function hashedModelOrFail(string $field): Model
    {
        $model = $this->getHashIdFieldModel($field);
        $value = $this->input($field);

        if ($value === null || $value === '') {
            throw (new ModelNotFoundException)->setModel($model);
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf('Hash ID field `%s` must be decoded to an integer before resolving a model.', $field));
        }

        return $model::findByHashIdValueOrFail($value);
    }

    /**
     * Resolve a configured hash ID field to its corresponding model collection.
     *
     * @return Collection<int, Model>
     */
    public function hashedModels(string $field): Collection
    {
        $model = $this->getHashIdFieldModel($field);
        $value = $this->input($field);

        if ($value === null || $value === '') {
            return (new $model)->newCollection();
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException(sprintf('Hash ID field `%s` must be decoded to an array of integers before resolving models.', $field));
        }

        $decodedValues = [];

        foreach ($value as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            if (!is_int($item)) {
                throw new InvalidArgumentException(sprintf('Hash ID field `%s` contains a non-integer decoded value.', $field));
            }

            $decodedValues[] = $item;
        }

        return $model::findManyByHashIdValues($decodedValues);
    }

    protected function passedValidation(): void
    {
        if (is_callable('parent::passedValidation')) {
            parent::passedValidation();
        }

        if (!$this->hashIdHttpEnabled()) {
            return;
        }

        $input = $this->all();

        foreach ($this->getHashIdFields() as $field => $model) {
            if (!Arr::has($input, $field)) {
                continue;
            }

            Arr::set($input, $field, $this->decodeHashIdFieldValue(Arr::get($input, $field), $model));
        }

        $this->replace($input);
    }

    /**
     * @return array<string, class-string<Model>>
     */
    protected function getHashIdFields(): array
    {
        $fields = [];

        if (property_exists($this, 'hashIdFields')) {
            /** @var array<string, class-string<Model>> $propertyFields */
            $propertyFields = $this->hashIdFields;

            $fields = $propertyFields;
        }

        foreach ((new ReflectionClass($this))->getAttributes(HashIdField::class) as $attribute) {
            /** @var HashIdField $instance */
            $instance = $attribute->newInstance();

            $fields[$instance->field] = $instance->model;
        }

        return $fields;
    }

    /**
     * @return class-string<Model>
     */
    protected function getHashIdFieldModel(string $field): string
    {
        $model = $this->getHashIdFields()[$field] ?? null;

        if (!is_string($model)) {
            throw new InvalidArgumentException(sprintf('Hash ID field `%s` is not configured for `%s`.', $field, static::class));
        }

        return $model;
    }

    protected function decodeHashIdFieldValue(mixed $value, string $model): mixed
    {
        if (is_array($value)) {
            return array_map(
                fn (mixed $item): mixed => $this->decodeHashIdFieldValue($item, $model),
                $value,
            );
        }

        if ($value === null || $value === '') {
            return $value;
        }

        return $model::decodeHashId($value);
    }

    protected function hashIdHttpEnabled(): bool
    {
        return (bool)Config::get('hashid.http_enabled', true);
    }
}
