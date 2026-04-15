<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Concerns\HasHashIdRouting;

class TestUserWithRouteBinding extends TestUser
{
    use HasHashIdRouting;
}
