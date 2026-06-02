<?php

namespace Atldays\HashIds\Concerns;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Atldays\HashIds\Exceptions\ModelNotFoundByHashIdException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

trait HasHashIdQueries
{
    protected static function assertHashIdString(string $value): void
    {
        if ($value === '') {
            throw InvalidHashIdException::forModel(static::class, $value);
        }
    }

    /**
     * Decode a config-aware external hash ID input into its source value.
     */
    protected static function decodeHashIdInput(int|string|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (Config::get('hashid.enabled', true)) {
            return static::decodeHashId($value);
        }

        if (is_int($value)) {
            return $value;
        }

        if (ctype_digit($value)) {
            return (int)$value;
        }

        throw InvalidHashIdException::forModel(static::class, $value);
    }

    /**
     * Find a model by its decoded hash ID source value.
     */
    public static function findByHashIdValue(int $value): ?static
    {
        return static::query()
            ->where(static::getQualifiedHashIdColumn(), $value)
            ->first();
    }

    /**
     * Find a model by its decoded hash ID source value or fail.
     */
    public static function findByHashIdValueOrFail(int $value): static
    {
        return static::query()
            ->where(static::getQualifiedHashIdColumn(), $value)
            ->firstOrFail();
    }

    /**
     * Find multiple models by their decoded hash ID source values.
     *
     * @param array<int, int> $values
     * @return Collection<int, static>
     */
    public static function findManyByHashIdValues(array $values): Collection
    {
        $ids = array_values(array_unique($values));

        if ($ids === []) {
            return static::query()->getModel()->newCollection();
        }

        /** @var Collection<int, static> $models */
        $models = static::query()
            ->whereIn(static::getQualifiedHashIdColumn(), $ids)
            ->get()
            ->keyBy(static fn (self $instance): mixed => $instance->getAttribute(static::getHashIdColumn()));

        return static::query()->getModel()->newCollection(
            array_values(
                array_filter(
                    array_map(
                        static fn (int $id): ?self => $models->get($id),
                        $ids,
                    ),
                ),
            ),
        );
    }

    /**
     * Find a model by a config-aware external hash ID input.
     *
     * @throws InvalidHashIdException
     */
    public static function findByHashIdInput(int|string|null $value): ?static
    {
        $id = static::decodeHashIdInput($value);

        if ($id === null) {
            return null;
        }

        return static::findByHashIdValue($id);
    }

    /**
     * Find multiple models by config-aware external hash ID inputs.
     *
     * @param array<int, int|string|null> $values
     * @return Collection<int, static>
     *
     * @throws InvalidHashIdException
     */
    public static function findManyByHashIdInput(array $values): Collection
    {
        return static::query()
            ->whereHashIdInputs($values)
            ->get();
    }

    /**
     * Find a model by a config-aware external hash ID input or fail with a dedicated exception.
     *
     * @throws InvalidHashIdException
     * @throws ModelNotFoundByHashIdException
     */
    public static function findOrFailByHashIdInput(int|string|null $value): static
    {
        $id = static::decodeHashIdInput($value);

        if ($id !== null) {
            $model = static::findByHashIdValue($id);

            if ($model instanceof static) {
                return $model;
            }
        }

        throw ModelNotFoundByHashIdException::forModel(static::class, $value, $id);
    }

    /**
     * Find a model by its hash ID.
     *
     * @throws InvalidHashIdException
     */
    public static function findByHashId(string $value): ?static
    {
        static::assertHashIdString($value);

        $id = static::decodeHashId($value);

        return static::findByHashIdValue($id);
    }

    /**
     * Find multiple models by their hash IDs.
     *
     * @param array<int, string> $values
     * @return Collection<int, static>
     *
     * @throws InvalidHashIdException
     */
    public static function findManyByHashId(array $values): Collection
    {
        foreach ($values as $value) {
            static::assertHashIdString($value);
        }

        return static::query()
            ->whereHashIds($values)
            ->get();
    }

    /**
     * Find a model by its hash ID or fail with a dedicated exception.
     *
     * @throws InvalidHashIdException
     * @throws ModelNotFoundByHashIdException
     */
    public static function findOrFailByHashId(string $value): static
    {
        static::assertHashIdString($value);

        $id = static::decodeHashId($value);

        $model = static::findByHashIdValue($id);

        if ($model instanceof static) {
            return $model;
        }

        throw ModelNotFoundByHashIdException::forModel(static::class, $value, $id);
    }

    /**
     * Find a model by its hash ID or execute a fallback callback.
     *
     * @throws InvalidHashIdException
     */
    public static function findOrByHashId(string $value, Closure $callback): mixed
    {
        $model = static::findByHashId($value);

        if ($model instanceof static) {
            return $model;
        }

        return $callback();
    }

    /**
     * Find a model by its hash ID or return a fresh model instance.
     *
     * @param array<string, mixed> $attributes
     *
     * @throws InvalidHashIdException
     */
    public static function findOrNewByHashId(string $value, array $attributes = []): static
    {
        $model = static::findByHashId($value);

        if ($model instanceof static) {
            return $model;
        }

        return static::query()->newModelInstance($attributes);
    }

    /**
     * Scope a query by a single hash ID.
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashId(Builder $query, string $value): Builder
    {
        static::assertHashIdString($value);

        $id = static::decodeHashId($value);

        return $query->where(static::getQualifiedHashIdColumn(), $id);
    }

    /**
     * Scope a query by excluding a single hash ID.
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashIdNot(Builder $query, string $value): Builder
    {
        static::assertHashIdString($value);

        $id = static::decodeHashId($value);

        return $query->where(static::getQualifiedHashIdColumn(), '!=', $id);
    }

    /**
     * Scope a query by a config-aware external hash ID input.
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashIdInput(Builder $query, int|string|null $value): Builder
    {
        $id = static::decodeHashIdInput($value);

        if ($id === null) {
            return $query->whereIn(static::getQualifiedHashIdColumn(), []);
        }

        return $query->where(static::getQualifiedHashIdColumn(), $id);
    }

    /**
     * Scope a query by excluding a config-aware external hash ID input.
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashIdInputNot(Builder $query, int|string|null $value): Builder
    {
        $id = static::decodeHashIdInput($value);

        if ($id === null) {
            return $query;
        }

        return $query->where(static::getQualifiedHashIdColumn(), '!=', $id);
    }

    /**
     * Scope a query by multiple hash IDs.
     *
     * @param array<int, string> $values
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashIds(Builder $query, array $values): Builder
    {
        $ids = [];

        foreach ($values as $value) {
            static::assertHashIdString($value);

            $id = static::decodeHashId($value);

            $ids[] = $id;
        }

        return $query->whereIn(static::getQualifiedHashIdColumn(), array_values(array_unique($ids)));
    }

    /**
     * Scope a query by multiple config-aware external hash ID inputs.
     *
     * @param array<int, int|string|null> $values
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashIdInputs(Builder $query, array $values): Builder
    {
        $ids = [];

        foreach ($values as $value) {
            $id = static::decodeHashIdInput($value);

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $query->whereIn(static::getQualifiedHashIdColumn(), array_values(array_unique($ids)));
    }

    /**
     * Scope a query by excluding multiple config-aware external hash ID inputs.
     *
     * @param array<int, int|string|null> $values
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashIdInputsNot(Builder $query, array $values): Builder
    {
        $ids = [];

        foreach ($values as $value) {
            $id = static::decodeHashIdInput($value);

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $query->whereNotIn(static::getQualifiedHashIdColumn(), array_values(array_unique($ids)));
    }

    /**
     * Scope a query by excluding multiple hash IDs.
     *
     * @param array<int, string> $values
     *
     * @throws InvalidHashIdException
     */
    public function scopeWhereHashIdsNot(Builder $query, array $values): Builder
    {
        $ids = [];

        foreach ($values as $value) {
            static::assertHashIdString($value);

            $id = static::decodeHashId($value);

            $ids[] = $id;
        }

        return $query->whereNotIn(static::getQualifiedHashIdColumn(), array_values(array_unique($ids)));
    }
}
