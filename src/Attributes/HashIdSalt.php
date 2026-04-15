<?php

namespace Atldays\HashIds\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class HashIdSalt
{
    public function __construct(
        public string $salt,
    ) {}
}
