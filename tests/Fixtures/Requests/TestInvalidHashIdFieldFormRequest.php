<?php

namespace Atldays\HashIds\Tests\Fixtures\Requests;

use Atldays\HashIds\Http\Concerns\InteractsWithHashIds;
use Illuminate\Foundation\Http\FormRequest;
use stdClass;

class TestInvalidHashIdFieldFormRequest extends FormRequest
{
    use InteractsWithHashIds;

    /**
     * @var array<string, class-string>
     */
    protected array $hashIdFields = [
        'author' => stdClass::class,
    ];

    public function rules(): array
    {
        return [];
    }

    public function resolvedHashIdFields(): array
    {
        return $this->getHashIdFields();
    }
}
