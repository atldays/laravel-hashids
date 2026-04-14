<?php

namespace Atldays\HashIds;

use Illuminate\Support\Facades\Facade;

class HashIdRepository extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return HashIdVault::class;
    }
}
