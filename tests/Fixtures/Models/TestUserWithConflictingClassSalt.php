<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Attributes\HashIdSalt;
use Atldays\HashIds\Attributes\HashIdSaltFromTable;
use Atldays\HashIds\Concerns\HasHashId;
use Illuminate\Database\Eloquent\Model;

#[HashIdSalt('model-salt')]
#[HashIdSaltFromTable]
class TestUserWithConflictingClassSalt extends Model
{
    use HasHashId;

    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
