<?php

namespace Atldays\HashIds\Attributes\Support;

use Atldays\HashIds\Attributes\HashIdColumn;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionException;

class ColumnResolver extends AbstractResolver
{
    protected array $attributeClasses = [HashIdColumn::class];

    public function handle(): string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->targetClass;
        $model = new $modelClass;
        $attribute = $this->resolveAttribute($this->reflection());

        if ($attribute instanceof HashIdColumn) {
            return $attribute->column;
        }

        if (property_exists($model, 'hashIdColumn')) {
            $column = $this->resolvePropertyColumn($model);

            if (is_string($column) && $column !== '') {
                return $column;
            }
        }

        return $model->getKeyName();
    }

    protected function resolvePropertyColumn(Model $model): mixed
    {
        try {
            return (new ReflectionClass($model))->getProperty('hashIdColumn')->getValue($model);
        } catch (ReflectionException) {
            return null;
        }
    }
}
