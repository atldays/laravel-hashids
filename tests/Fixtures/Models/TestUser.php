<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Concerns\HasHashId;
use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    use HasHashId;

    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
