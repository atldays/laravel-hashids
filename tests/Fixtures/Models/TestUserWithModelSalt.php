<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Attributes\HashIdSalt;
use Atldays\HashIds\Tests\Fixtures\Traits\TestHashIdSaltTrait;

#[HashIdSalt('model-salt')]
class TestUserWithModelSalt extends TestUser
{
    use TestHashIdSaltTrait;
}
