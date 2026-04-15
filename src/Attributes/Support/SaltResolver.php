<?php

namespace Atldays\HashIds\Attributes\Support;

use Atldays\HashIds\Attributes\HashIdSalt;
use Atldays\HashIds\Attributes\HashIdSaltFromClass;
use Atldays\HashIds\Attributes\HashIdSaltFromTable;
use Illuminate\Database\Eloquent\Model;

class SaltResolver extends AbstractResolver
{
    protected array $attributeClasses = [
        HashIdSalt::class,
        HashIdSaltFromClass::class,
        HashIdSaltFromTable::class,
    ];

    public function handle(): string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->targetClass;
        $attribute = $this->resolveAttribute($this->reflection());

        if ($attribute instanceof HashIdSalt) {
            return $attribute->salt;
        }

        if ($attribute instanceof HashIdSaltFromTable) {
            return (new $modelClass)->getTable();
        }

        if ($attribute instanceof HashIdSaltFromClass) {
            return $modelClass;
        }

        return $modelClass;
    }
}
