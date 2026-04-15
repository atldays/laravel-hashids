<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

class TestUserWithRouteBindingMethodOverride extends TestUser
{
    protected bool $usesHashIdRouteBinding = false;

    protected function usesHashIdRouteBinding(): bool
    {
        return true;
    }
}
