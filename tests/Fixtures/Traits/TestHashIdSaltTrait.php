<?php

namespace Atldays\HashIds\Tests\Fixtures\Traits;

use Atldays\HashIds\Attributes\HashIdSalt;

#[HashIdSalt('trait-salt')]
trait TestHashIdSaltTrait {}
