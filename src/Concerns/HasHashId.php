<?php

namespace Atldays\HashIds\Concerns;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Atldays\HashIds\HashId;
use Atldays\HashIds\HashIdRegistry;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @phpstan-require-extends Model
 */
trait HasHashId
{
    use HasHashIdQueries;

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
    public static function encodeHashId(?int $id): ?string
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

        if (!is_string($value)) {
            throw InvalidHashIdException::forModel(static::class, $value);
        }

        return static::hashIdInstance()->decode($value);
    }

    /**
     * Create or reuse the hash ID service instance for the current model.
     */
    protected static function hashIdInstance(): HashId
    {
        return HashIdRegistry::make(static::getHashIdSalt(), static::class);
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
     * Get the fully qualified database column used for reverse lookups from hash IDs.
     */
    protected static function getQualifiedHashIdColumn(): string
    {
        return (new static)->qualifyColumn(static::getHashIdColumn());
    }

    /**
     * Get the hashed representation for the current model instance.
     */
    public function getHashId(): ?string
    {
        $key = $this->getHashIdValue();

        return is_int($key) ? static::encodeHashId($key) : null;
    }

    /**
     * Accessor for the computed hash ID attribute.
     */
    public function getHashIdAttribute(): ?string
    {
        return $this->getHashId();
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
