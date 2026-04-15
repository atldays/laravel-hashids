<?php

namespace Atldays\HashIds\Http\Concerns;

use Atldays\HashIds\Concerns\HasHashId;
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
        /** @var $model Model&HasHashId */
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
        /** @var $model Model&HasHashId */
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

        if (!Config::get('hashid.enabled', true)) {
            return;
        }

        $input = $this->all();

        foreach ($this->getHashIdFields() as $field => $model) {
            foreach ($this->resolveHashIdFieldPaths($input, $field) as $resolvedField) {
                Arr::set($input, $resolvedField, $this->decodeHashIdFieldValue(Arr::get($input, $resolvedField), $model));
            }
        }

        $this->replace($input);
    }

    /**
     * @return array<string, class-string<Model&HasHashId>>
     */
    protected function getHashIdFields(): array
    {
        $fields = [];

        if (property_exists($this, 'hashIdFields')) {
            /** @var array<string, class-string<Model&HasHashId>> $propertyFields */
            $propertyFields = $this->hashIdFields;

            $fields = $propertyFields;
        }

        foreach ($this->getHashIdFieldAttributes() as $attribute) {
            $fields[$attribute->field] = $attribute->model;
        }

        return $fields;
    }

    /**
     * @return array<int, HashIdField>
     */
    protected function getHashIdFieldAttributes(): array
    {
        $classes = [];
        $reflection = new ReflectionClass($this);

        do {
            $classes[] = $reflection;
            $reflection = $reflection->getParentClass();
        } while ($reflection !== false);

        $attributes = [];

        foreach (array_reverse($classes) as $class) {
            foreach ($class->getAttributes(HashIdField::class) as $attribute) {
                /** @var HashIdField $instance */
                $instance = $attribute->newInstance();

                $attributes[] = $instance;
            }
        }

        return $attributes;
    }

    /**
     * @return class-string<Model&HasHashId>
     */
    protected function getHashIdFieldModel(string $field): string
    {
        $model = $this->getHashIdFields()[$field] ?? null;

        if (!is_string($model)) {
            foreach ($this->getHashIdFields() as $pattern => $patternModel) {
                if ($this->hashIdFieldMatches($pattern, $field)) {
                    $model = $patternModel;

                    break;
                }
            }
        }

        if (!is_string($model)) {
            throw new InvalidArgumentException(sprintf('Hash ID field `%s` is not configured for `%s`.', $field, static::class));
        }

        return $model;
    }

    /**
     * Decode a configured hash ID field value to its plain model value.
     *
     * @param class-string<Model&HasHashId> $model
     */
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

    /**
     * @return array<int, string>
     */
    protected function resolveHashIdFieldPaths(array $input, string $field): array
    {
        if (!str_contains($field, '*')) {
            return Arr::has($input, $field) ? [$field] : [];
        }

        return $this->resolveWildcardHashIdFieldPaths($input, explode('.', $field));
    }

    protected function hashIdFieldMatches(string $pattern, string $field): bool
    {
        $patternSegments = explode('.', $pattern);
        $fieldSegments = explode('.', $field);

        if (count($patternSegments) !== count($fieldSegments)) {
            return false;
        }

        foreach ($patternSegments as $index => $segment) {
            if ($segment !== '*' && $segment !== $fieldSegments[$index]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string> $segments
     * @param array<int, string> $prefix
     * @return array<int, string>
     */
    protected function resolveWildcardHashIdFieldPaths(mixed $value, array $segments, array $prefix = []): array
    {
        if ($segments === []) {
            return [implode('.', $prefix)];
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            if (!is_array($value)) {
                return [];
            }

            $paths = [];

            foreach (array_keys($value) as $key) {
                $paths = array_merge(
                    $paths,
                    $this->resolveWildcardHashIdFieldPaths($value[$key], $segments, [...$prefix, (string)$key]),
                );
            }

            return $paths;
        }

        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return [];
        }

        return $this->resolveWildcardHashIdFieldPaths($value[$segment], $segments, [...$prefix, $segment]);
    }
}
