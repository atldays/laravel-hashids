<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Concerns\HasHashId;
use Atldays\HashIds\Contracts\HasHashIdModel;
use Illuminate\Database\Eloquent\Model;

class TestUserAsContract extends Model implements HasHashIdModel
{
    use HasHashId;

    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
