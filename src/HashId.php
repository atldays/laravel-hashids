<?php

namespace Atldays\HashIds;

use Hashids\Hashids;

class HashId
{
    private ?Hashids $hashIds = null;

    private string $salt = '';

    public static function instance(string $salt, ?string $key = null): HashId
    {
        return HashIdRegistry::make($salt, $key);
    }

    public function __construct(
        private readonly int $minLength = 12,
    ) {}

    public function hashIds(): Hashids
    {
        if ($this->hashIds instanceof Hashids) {
            return $this->hashIds;
        }

        return $this->hashIds = new Hashids($this->salt, $this->minLength);
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function encode(?int $id): int|string|null
    {
        return $this->hashIds()->encodeHex($id);
    }

    public function decode(int|string $hash): int
    {
        return (int)$this->hashIds()->decodeHex($hash);
    }
}
