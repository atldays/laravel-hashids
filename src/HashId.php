<?php

namespace Atldays\HashIds;

use Atldays\HashIds\Exceptions\InvalidHashIdException;
use Hashids\Hashids;

class HashId
{
    private ?Hashids $hashIds = null;

    public function __construct(
        private readonly string $salt = '',
        private readonly int $length = 0,
        private readonly string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
    ) {}

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
