<?php

namespace Atldays\HashIds;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;

class HashIdVault
{
    /**
     * @var array<string|class-string, HashId>
     */
    private array $items = [];

    /**
     * @throws BindingResolutionException
     */
    public function make(string $salt, ?string $key = null): HashId
    {
        if ($key === null || $key === '') {
            $key = $salt;
        }

        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $this->items[$key] = App::make(HashId::class, ['salt' => $salt]);
    }
}
