<?php

namespace App\Storage;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface CacheInterface
{
    public function deleteCache(string $path): bool;

    public function getImageResponse(string $path, array $params): ResponseInterface;
}
