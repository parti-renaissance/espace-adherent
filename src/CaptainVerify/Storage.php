<?php

namespace App\CaptainVerify;

use Psr\SimpleCache\CacheInterface;

class Storage
{
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public function store(string $email, Response $response): void
    {
        $this->cache->set($this->getKey($email), $response);
    }

    public function get(string $email): ?Response
    {
        return $this->cache->get($this->getKey($email));
    }

    private function getKey(string $email): string
    {
        return md5(mb_strtolower($email));
    }
}
