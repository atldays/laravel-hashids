<?php

namespace Atldays\HashIds;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Hashids\Hashids;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;

class HashId
{
    private ?Hashids $hashIds = null;

    public function __construct(
        private readonly string $salt = '',
        private readonly int $length = 0,
        private readonly string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public static function make(
        ?string $salt = null,
        ?int $length = null,
        ?string $alphabet = null,
    ): self {
        return App::make(self::class, array_filter([
            'salt' => $salt,
            'length' => $length,
            'alphabet' => $alphabet,
        ], static fn (mixed $value): bool => $value !== null));
    }

    public function hashIds(): Hashids
    {
        return $this->hashIds ??= new Hashids($this->salt, $this->length, $this->alphabet);
    }

    public function encode(int $id): string
    {
        return $this->hashIds()->encodeHex($id);
    }

    /**
     * @throws InvalidHashIdException
     */
    public function decode(string $hash): int
    {
        $decoded = $this->hashIds()->decodeHex($hash);

        if (!is_string($decoded) || $decoded === '' || !ctype_digit($decoded)) {
            throw InvalidHashIdException::forHashId($hash);
        }

        return (int)$decoded;
    }
}
