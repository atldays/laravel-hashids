<?php

namespace Atldays\HashIds\Attributes\Support;

use ReflectionClass;

abstract class AbstractResolver
{
    /**
     * @var array<int, class-string>
     */
    protected array $attributeClasses = [];

    /**
     * @param class-string $targetClass
     */
    public function __construct(
        protected string $targetClass,
    ) {}

    /**
     * @param class-string $targetClass
     */
    public static function resolve(string $targetClass): mixed
    {
        return (new static($targetClass))->handle();
    }

    abstract public function handle(): mixed;

    protected function reflection(): ReflectionClass
    {
        return new ReflectionClass($this->targetClass);
    }

    protected function resolveAttribute(ReflectionClass $class): ?object
    {
        $attributes = $this->collectAttributes($class);

        if ($attributes === []) {
            return null;
        }

        return $attributes[array_key_last($attributes)];
    }

    /**
     * @return array<int, object>
     */
    protected function resolveOwnAttributes(ReflectionClass $class): array
    {
        $attributes = [];

        foreach ($this->attributeClasses() as $attributeClass) {
            foreach ($class->getAttributes($attributeClass) as $attribute) {
                $attributes[] = $attribute->newInstance();
            }
        }

        return $attributes;
    }

    /**
     * @return array<int, object>
     */
    protected function collectAttributes(ReflectionClass $class): array
    {
        $attributes = [];

        if ($parent = $class->getParentClass()) {
            $attributes = array_merge($attributes, $this->collectAttributes($parent));
        }

        $visitedTraits = [];
        $attributes = array_merge($attributes, $this->collectTraitAttributes($class, $visitedTraits));

        foreach ($this->attributeClasses() as $attributeClass) {
            foreach ($class->getAttributes($attributeClass) as $attribute) {
                $attributes[] = $attribute->newInstance();
            }
        }

        return $attributes;
    }

    /**
     * @param array<string, true> $visitedTraits
     * @return array<int, object>
     */
    protected function collectTraitAttributes(ReflectionClass $class, array &$visitedTraits): array
    {
        $attributes = [];

        foreach ($class->getTraits() as $trait) {
            $traitName = $trait->getName();

            if (array_key_exists($traitName, $visitedTraits)) {
                continue;
            }

            $visitedTraits[$traitName] = true;

            $attributes = array_merge($attributes, $this->collectTraitAttributes($trait, $visitedTraits));

            foreach ($this->attributeClasses() as $attributeClass) {
                foreach ($trait->getAttributes($attributeClass) as $attribute) {
                    $attributes[] = $attribute->newInstance();
                }
            }
        }

        return $attributes;
    }

    /**
     * @return array<int, class-string>
     */
    protected function attributeClasses(): array
    {
        return $this->attributeClasses;
    }
}
