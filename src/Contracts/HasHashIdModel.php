<?php

namespace Atldays\HashIds\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface HasHashIdModel
{
    public static function getHashIdSalt(): string;

    public static function encodeHashId(?int $id): ?string;

    public static function decodeHashId(int|string|null $value): ?int;

    public static function findByHashIdValue(int $value): ?static;

    public static function findByHashIdValueOrFail(int $value): static;

    /**
     * @param array<int, int> $values
     * @return Collection<int, Model>
     */
    public static function findManyByHashIdValues(array $values): Collection;

    public static function findByHashId(string $value): ?static;

    /**
     * @param array<int, string> $values
     * @return Collection<int, Model>
     */
    public static function findManyByHashId(array $values): Collection;

    public static function findOrFailByHashId(string $value): static;

    public static function findOrByHashId(string $value, Closure $callback): mixed;

    /**
     * @param array<string, mixed> $attributes
     */
    public static function findOrNewByHashId(string $value, array $attributes = []): static;

    public static function findByHashIdInput(int|string|null $value): ?static;

    /**
     * @param array<int, int|string|null> $values
     * @return Collection<int, Model>
     */
    public static function findManyByHashIdInput(array $values): Collection;

    public static function findOrFailByHashIdInput(int|string|null $value): static;

    public function getHashId(): ?string;

    public function getHashIdInput(): int|string|null;

    public function getHashIdAttribute(): ?string;
}
