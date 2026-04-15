<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Tests\Fixtures\Traits\TestHashIdSaltTrait;

class TestUserWithTraitSalt extends TestUser
{
    use TestHashIdSaltTrait;
}
