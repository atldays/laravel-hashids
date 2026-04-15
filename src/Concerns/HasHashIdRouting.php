<?php

namespace Atldays\HashIds\Concerns;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

trait HasHashIdRouting
{
    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey(): mixed
    {
        if (!(bool)Config::get('hashid.http_enabled', true)) {
            return parent::getRouteKey();
        }

        return $this->getHashId();
    }

    /**
     * Resolve a route binding using either the default Laravel behavior or hash IDs.
     *
     * @throws InvalidHashIdException
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (
            !(bool)Config::get('hashid.http_enabled', true)
            && (is_int($value) || (is_string($value) && ctype_digit($value)))
        ) {
            $resolvedField = is_string($field) ? $field : $this->getRouteKeyName();

            return $this->resolveRouteBindingQuery($this, $value, $resolvedField)->first();
        }

        /** @var Model|null $model */
        $model = static::findByHashId($value);

        return $model;
    }
}
