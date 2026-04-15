<?php

namespace Atldays\HashIds\Exceptions;

use InvalidArgumentException;

class InvalidHashIdException extends InvalidArgumentException
{
    public static function forModel(string $model, int|string $value): self
    {
        return new self(sprintf('Unable to decode hash ID `%s` for model `%s`.', $value, $model));
    }

    public static function forHashId(int|string $value): self
    {
        return new self(sprintf('Unable to decode hash ID `%s`.', $value));
    }
}
