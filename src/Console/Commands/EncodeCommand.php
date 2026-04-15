<?php

namespace Atldays\HashIds\Console\Commands;

use Atldays\HashIds\Concerns\HasHashId;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class EncodeCommand extends Command
{
    protected $signature = 'hashid:encode
        {model : The Eloquent model class to use}
        {value : The numeric value to encode}';

    protected $description = 'Encode a numeric value into a model-specific hash ID.';

    public function handle(): int
    {
        /** @var $model Model&HasHashId */
        $model = $this->argument('model');
        $value = $this->argument('value');

        if (!is_string($model) || !$this->isValidModel($model)) {
            $this->components->error('The model must be an Eloquent model class that uses the HasHashId trait.');

            return self::FAILURE;
        }

        if (!is_string($value) || !ctype_digit($value)) {
            $this->components->error('The value must be a non-negative integer.');

            return self::FAILURE;
        }

        $this->line((string)$model::encodeHashId((int)$value));

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
