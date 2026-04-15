<?php

namespace Atldays\HashIds\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class ModelNotFoundByHashIdException extends ModelNotFoundException
{
    public static function forModel(string $model, int|string|null $hash, ?int $decodedId = null): self
    {
        $exception = new self;

        $message = sprintf('No query results for model [%s] by hash ID', $model);

        if ($hash !== null) {
            $message .= sprintf(' [%s]', $hash);
        }

        if ($decodedId !== null) {
            $message .= sprintf(' (decoded ID: %d)', $decodedId);
        }

        $exception->setModel($model, $decodedId !== null ? [$decodedId] : []);
        $exception->message = $message.'.';

        return $exception;
    }
}
