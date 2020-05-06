<?php

namespace App\ValueObject;

final class SHA1
{
    private $hash;

    private function __construct(string $hash)
    {
        if (!preg_match('/^[a-f0-9]{40}/i', $hash)) {
            throw new \InvalidArgumentException(sprintf('The given hash "%s" does not have a valid SHA-1 format.', $hash));
        }

        $this->hash = $hash;
    }

    public static function hash(string $string)
    {
        return new self(sha1($string));
    }

    public static function fromString(string $hash, $preserveCase = false): self
    {
        if (!$preserveCase) {
            $hash = mb_strtolower($hash);
        }

        return new self($hash);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function equals(self $other, $strict = false): bool
    {
        if (!$strict) {
            return hash_equals(mb_strtolower($this->hash), mb_strtolower($other->getHash()));
        }

        return hash_equals($this->hash, $other->getHash());
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
