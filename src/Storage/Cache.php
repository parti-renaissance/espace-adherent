<?php

namespace App\Storage;

use League\Glide\Responses\ResponseFactoryInterface;
use League\Glide\Server;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Cache implements CacheInterface
{
    private Server $glide;

    public function __construct(Server $glide)
    {
        $this->glide = $glide;
    }

    public function deleteCache(string $path): bool
    {
        return $this->glide->deleteCache($path);
    }

    public function setResponseFactory(ResponseFactoryInterface $responseFactory): void
    {
        $this->glide->setResponseFactory($responseFactory);
    }

    public function getImageResponse(string $path, array $params): ResponseInterface
    {
        return $this->glide->getImageResponse($path, $params);
    }
}
