<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Concerns\HasHashId;
use Atldays\HashIds\Tests\Fixtures\Traits\TestHashIdSaltFromClassTrait;
use Atldays\HashIds\Tests\Fixtures\Traits\TestHashIdSaltFromTableTrait;
use Illuminate\Database\Eloquent\Model;

class TestUserWithConflictingTraitSalt extends Model
{
    use HasHashId;
    use TestHashIdSaltFromClassTrait;
    use TestHashIdSaltFromTableTrait;

    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
