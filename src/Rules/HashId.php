<?php

namespace Atldays\HashIds\Rules;

use Atldays\HashIds\Exceptions\InvalidHashIdException;

class HashId extends AbstractRule
{
    protected function singleValidationMessageKey(): string
    {
        return 'laravel-hashids::validation.hash_id';
    }

    protected function multipleValidationMessageKey(): string
    {
        return 'laravel-hashids::validation.hash_ids';
    }

    protected function passesValue(mixed $value): bool
    {
        if ($this->isSkippableValue($value)) {
            return true;
        }

        if (!$this->isEnabled()) {
            return $this->isPlainValue($value);
        }

        if (!is_int($value) && !is_string($value)) {
            return false;
        }

        try {
            $decoded = $this->model::decodeHashId($value);

            return $decoded === null || $decoded > 0;
        } catch (InvalidHashIdException) {
            return false;
        }
    }
}
