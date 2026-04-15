<?php

namespace Atldays\HashIds\Http\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Model;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
readonly class HashIdField
{
    /**
     * @param  class-string<Model>  $model
     */
    public function __construct(
        public string $field,
        public string $model,
    ) {}
}
