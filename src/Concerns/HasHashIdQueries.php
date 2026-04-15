<?php

namespace Atldays\HashIds\Concerns;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Atldays\HashIds\Exceptions\ModelNotFoundByHashIdException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait HasHashIdQueries
{
    protected static function assertHashIdString(string $value): void
    {
        if ($value === '') {
            throw InvalidHashIdException::forModel(static::class, $value);
        }
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
            return (new static)->newCollection();
        }

        /** @var Collection<int, static> $models */
        $models = static::query()
            ->whereIn(static::getQualifiedHashIdColumn(), $ids)
            ->get()
            ->keyBy(static fn (self $instance): mixed => $instance->getAttribute(static::getHashIdColumn()));

        return (new static)->newCollection(
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

        return new static($attributes);
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
