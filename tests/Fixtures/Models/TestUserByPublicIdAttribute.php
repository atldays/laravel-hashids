<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Attributes\HashIdColumn;

#[HashIdColumn('public_id')]
class TestUserByPublicIdAttribute extends TestUser
{
    protected string $hashIdColumn = 'id';
}
