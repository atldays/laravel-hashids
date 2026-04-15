<?php

namespace Atldays\HashIds\Concerns;

use Illuminate\Support\Facades\Config;

trait SerializesHashId
{
    /**
     * Serialize the model attributes with the hash ID replacing the source column.
     *
     * @return array<string, mixed>
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (!Config::get('hashid.enabled', true)) {
            return $attributes;
        }

        $column = static::getHashIdColumn();

        if (!array_key_exists($column, $attributes)) {
            return $attributes;
        }

        $attributes[$column] = $this->getHashId();

        return $attributes;
    }
}
