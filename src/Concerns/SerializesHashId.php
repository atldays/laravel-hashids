<?php

namespace Atldays\HashIds\Concerns;

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

        $column = static::getHashIdColumn();

        if (!array_key_exists($column, $attributes)) {
            return $attributes;
        }

        $attributes[$column] = $this->getHashIdInput();

        return $attributes;
    }
}
