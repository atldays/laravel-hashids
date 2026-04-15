<?php

namespace Atldays\HashIds\Concerns;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Atldays\HashIds\Exceptions\ModelNotFoundByHashIdException;
use Atldays\HashIds\HashId;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @phpstan-require-extends Model
 */
trait HasHashId
{
    /**
     * Get the salt used to build hash IDs for the current model.
     */
    public static function getHashIdSalt(): string
    {
        return static::class;
    }

    /**
     * Encode a numeric source value into a hash ID.
     */
    public static function encodeHashId(?int $id): int|string|null
    {
        if ($id === null) {
            return null;
        }

        return static::hashIdInstance()->encode($id);
    }

    /**
     * Decode a raw route or input value into the numeric source value.
     *
     * @throws InvalidHashIdException
     */
    public static function decodeHashId(int|string|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (!static::hashIdInstance()->isStrict() && ctype_digit($value)) {
            $id = (int)$value;

            return $id > 0 ? $id : null;
        }

        $decodedId = static::hashIdInstance()->decode($value);

        if ($decodedId <= 0) {
            throw InvalidHashIdException::forValue(static::class, $value);
        }

        return $decodedId;
    }

    /**
     * Find a model by its hash ID or compatible numeric source value.
     *
     *
     * @throws InvalidHashIdException
     */
    public static function findByHashId(int|string|null $value): ?static
    {
        $id = static::decodeHashId($value);

        if ($id === null) {
            return null;
        }

        return static::query()
            ->where(static::getHashIdColumn(), $id)
            ->first();
    }

    /**
     * Find a model by its hash ID or fail with a dedicated exception.
     *
     *
     * @throws InvalidHashIdException
     * @throws ModelNotFoundByHashIdException
     */
    public static function findOrFailByHashId(int|string|null $value): static
    {
        $id = static::decodeHashId($value);

        if ($id === null) {
            throw ModelNotFoundByHashIdException::forModel(static::class, $value);
        }

        $model = static::query()
            ->where(static::getHashIdColumn(), $id)
            ->first();

        if ($model instanceof static) {
            return $model;
        }

        throw ModelNotFoundByHashIdException::forModel(static::class, $value, $id);
    }

    /**
     * Create or reuse the hash ID service instance for the current model.
     */
    protected static function hashIdInstance(): HashId
    {
        return HashId::instance(static::getHashIdSalt(), static::class);
    }

    /**
     * Get the database column used for reverse lookups from hash IDs.
     */
    protected static function getHashIdColumn(): string
    {
        $model = new static;

        if (property_exists($model, 'hashIdColumn')) {
            return $model->hashIdColumn;
        }

        return $model->getKeyName();
    }

    /**
     * Determine whether the provided route binding value is a plain numeric key.
     */
    protected static function isNumericRouteBindingValue(mixed $value): bool
    {
        return is_int($value) || (is_string($value) && ctype_digit($value));
    }

    /**
     * Get the hashed representation for the current model instance.
     */
    public function getHashId(): int|string|null
    {
        $key = $this->getHashIdValue();

        return is_int($key) ? static::encodeHashId($key) : null;
    }

    /**
     * Accessor for the computed hash ID attribute.
     */
    public function getHashIdAttribute(): int|string|null
    {
        return $this->getHashId();
    }

    /**
     * Resolve a route binding using either the default Laravel behavior or hash IDs.
     *
     * @throws InvalidHashIdException
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (!$this->usesHashIdRouteBinding()) {
            return parent::resolveRouteBinding($value, $field);
        }

        $resolvedField = is_string($field) ? $field : $this->getRouteKeyName();

        if (!static::hashIdInstance()->isStrict() && static::isNumericRouteBindingValue($value)) {
            return $this->resolveRouteBindingQuery($this, $value, $resolvedField)->first();
        }

        /** @var Model|null $model */
        $model = static::findByHashId($value);

        return $model;
    }

    /**
     * Determine whether hash ID-aware route binding should be used for the model.
     */
    protected function usesHashIdRouteBinding(): bool
    {
        if (property_exists($this, 'usesHashIdRouteBinding')) {
            return (bool)$this->usesHashIdRouteBinding;
        }

        return false;
    }

    /**
     * Get the numeric source value that should be encoded for the current model.
     */
    protected function getHashIdValue(): ?int
    {
        $key = $this->getAttribute(static::getHashIdColumn());

        return is_int($key) ? $key : null;
    }
}
