<?php

namespace Atldays\HashIds;

use Illuminate\Support\Facades\Facade;

class HashIds extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return HashId::class;
    }
}
