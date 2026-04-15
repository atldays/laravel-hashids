<?php

namespace Atldays\HashIds;

use Illuminate\Support\Facades\Facade;

class HashIdRegistry extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return HashIdVault::class;
    }
}
