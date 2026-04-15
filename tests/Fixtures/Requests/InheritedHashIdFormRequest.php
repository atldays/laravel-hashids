<?php

namespace Atldays\HashIds\Tests\Fixtures\Requests;

use Atldays\HashIds\Http\Attributes\HashIdField;
use Atldays\HashIds\Tests\Fixtures\Models\TestUser;

#[HashIdField('inherited.users', TestUser::class)]
class InheritedHashIdFormRequest extends TestHashIdFormRequest {}
