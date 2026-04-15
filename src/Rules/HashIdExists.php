<?php

namespace Atldays\HashIds\Rules;

use Atldays\HashIds\Exceptions\InvalidHashIdException;

class HashIdExists extends AbstractRule
{
    protected function singleValidationMessageKey(): string
    {
        return 'laravel-hashids::validation.hash_id_exists';
    }

    protected function multipleValidationMessageKey(): string
    {
        return 'laravel-hashids::validation.hash_ids_exist';
    }

    protected function passesValue(mixed $value): bool
    {
        if ($this->isSkippableValue($value)) {
            return true;
        }

        if (!$this->isEnabled()) {
            if (!$this->isPlainValue($value)) {
                return false;
            }

            return $this->model::findByHashIdValue((int)$value) instanceof $this->model;
        }

        if (!is_int($value) && !is_string($value)) {
            return false;
        }

        try {
            return $this->model::findByHashId($value) instanceof $this->model;
        } catch (InvalidHashIdException) {
            return false;
        }
    }
}
