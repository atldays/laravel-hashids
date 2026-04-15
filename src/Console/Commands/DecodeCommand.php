<?php

namespace Atldays\HashIds\Console\Commands;

use Atldays\HashIds\Concerns\HasHashId;
use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class DecodeCommand extends Command
{
    protected $signature = 'hashid:decode
        {model : The Eloquent model class to use}
        {value : The hash ID value to decode}';

    protected $description = 'Decode a model-specific hash ID into its numeric value.';

    public function handle(): int
    {
        /** @var $model Model&HasHashId */
        $model = $this->argument('model');
        $value = $this->argument('value');

        if (!is_string($model) || !$this->isValidModel($model)) {
            $this->components->error('The model must be an Eloquent model class that uses the HasHashId trait.');

            return self::FAILURE;
        }

        if (!is_string($value) || $value === '') {
            $this->components->error('The value must be a non-empty hash ID string.');

            return self::FAILURE;
        }

        try {
            $decoded = $model::decodeHashId($value);

            $this->line((string)$decoded);
        } catch (InvalidHashIdException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @param class-string<Model> $model
     */
    protected function isValidModel(string $model): bool
    {
        return is_subclass_of($model, Model::class)
            && in_array(HasHashId::class, class_uses_recursive($model), true);
    }
}
