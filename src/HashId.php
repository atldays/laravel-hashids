<?php

namespace Atldays\HashIds;

use Hashids\Hashids;

class HashId
{
    private ?Hashids $hashIds = null;

    private string $salt = '';

    public static function instance(string $salt, ?string $key = null): HashId
    {
        return HashIdRepository::make($salt, $key);
    }

    public function __construct(
        private bool $enable = false,
        private bool $strict = false,
        private readonly int $minLength = 12,
    ) {}

    public function hashIds(): Hashids
    {
        if ($this->hashIds instanceof Hashids) {
            return $this->hashIds;
        }

        return $this->hashIds = new Hashids($this->salt, $this->minLength);
    }

    public function isDisabled(): bool
    {
        return ! $this->enable;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * @return $this
     */
    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;

        return $this;
    }

    /**
     * @return $this
     */
    public function setStrict(bool $strict): self
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function encode(?int $id): int|string|null
    {
        if (! $this->enable) {
            return $id;
        }

        return $this->hashIds()->encodeHex($id);
    }

    public function decode(int|string $hash): int
    {
        if (is_int($hash)) {
            return $hash;
        }

        return (int) $this->hashIds()->decodeHex($hash);
    }
}
