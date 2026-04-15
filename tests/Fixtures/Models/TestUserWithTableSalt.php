<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Attributes\HashIdSaltFromTable;

#[HashIdSaltFromTable]
class TestUserWithTableSalt extends TestUser {}
