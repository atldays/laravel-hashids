<?php

namespace Atldays\HashIds\Tests\Fixtures\Models;

use Atldays\HashIds\Concerns\HasHashId;
use Atldays\HashIds\Concerns\SerializesHashId;
use Illuminate\Database\Eloquent\Model;

class TestSerializedUserByPublicId extends Model
{
    use HasHashId;
    use SerializesHashId;

    protected string $hashIdColumn = 'public_id';

    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
