<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class TestUserWithoutHashId extends Model
{
    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
