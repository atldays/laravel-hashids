<?php

namespace Atldays\HashIds\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class HashIdColumn
{
    public function __construct(
        public string $column,
    ) {}
}
