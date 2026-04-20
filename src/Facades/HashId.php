<?php

namespace Atldays\HashIds\Facades;

use Atldays\HashIds\HashId as HashIdService;
use Illuminate\Support\Facades\Facade;

class HashId extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return HashIdService::class;
    }
}
