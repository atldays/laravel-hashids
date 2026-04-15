<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

class TestUserWithRouteBinding extends TestUser
{
    protected bool $usesHashIdRouteBinding = true;
}
