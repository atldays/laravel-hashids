<?php

namespace Atldays\HashIds;

class HashIdVault
{
    /**
     * @var array<string|class-string, HashId>
     */
    private array $items = [];

    public function make(string $salt, ?string $key = null): HashId
    {
        if (empty($key)) {
            $key = $salt;
        }

        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $this->items[$key] = app(HashId::class)->setSalt($salt);
    }
}
