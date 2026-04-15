<?php

namespace Atldays\HashIds\Attributes\Support;

use Atldays\HashIds\Attributes\HashIdSalt;
use Atldays\HashIds\Attributes\HashIdSaltFromClass;
use Atldays\HashIds\Attributes\HashIdSaltFromTable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use ReflectionClass;

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
        $attribute = $this->resolveSaltAttribute($this->reflection());

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

    protected function resolveSaltAttribute(ReflectionClass $class): ?object
    {
        $classes = [];

        do {
            $classes[] = $class;
            $class = $class->getParentClass() ?: null;
        } while ($class !== null);

        $resolved = null;

        foreach (array_reverse($classes) as $reflection) {
            $traitAttributes = $this->resolveTraitSaltAttributes($reflection);
            $classAttributes = $this->resolveOwnAttributes($reflection);

            if (count($traitAttributes) > 1) {
                throw new InvalidArgumentException(sprintf(
                    'Model `%s` has conflicting hash ID salt attributes on its traits.',
                    $this->targetClass,
                ));
            }

            if (count($classAttributes) > 1) {
                throw new InvalidArgumentException(sprintf(
                    'Model `%s` has conflicting hash ID salt attributes on the class itself.',
                    $this->targetClass,
                ));
            }

            if ($classAttributes !== []) {
                $resolved = $classAttributes[0];

                continue;
            }

            if ($traitAttributes !== []) {
                $resolved = $traitAttributes[0];
            }
        }

        return $resolved;
    }

    /**
     * @return array<int, object>
     */
    protected function resolveTraitSaltAttributes(ReflectionClass $class): array
    {
        $attributes = [];
        $visitedTraits = [];

        foreach ($class->getTraits() as $trait) {
            $attributes = array_merge($attributes, $this->resolveTraitSaltAttributesRecursively($trait, $visitedTraits));
        }

        return $attributes;
    }

    /**
     * @param array<string, true> $visitedTraits
     * @return array<int, object>
     */
    protected function resolveTraitSaltAttributesRecursively(ReflectionClass $trait, array &$visitedTraits): array
    {
        $traitName = $trait->getName();

        if (array_key_exists($traitName, $visitedTraits)) {
            return [];
        }

        $visitedTraits[$traitName] = true;

        $attributes = [];

        foreach ($trait->getTraits() as $nestedTrait) {
            $attributes = array_merge($attributes, $this->resolveTraitSaltAttributesRecursively($nestedTrait, $visitedTraits));
        }

        return array_merge($attributes, $this->resolveOwnAttributes($trait));
    }
}
