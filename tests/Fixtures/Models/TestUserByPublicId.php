<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Concerns\HasHashId;
use Illuminate\Database\Eloquent\Model;

class TestUserByPublicId extends Model
{
    use HasHashId;

    protected string $hashIdColumn = 'public_id';

    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
