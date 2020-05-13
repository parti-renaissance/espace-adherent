<?php

namespace App\Mailer;

use GuzzleHttp\ClientInterface as Guzzle;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractEmailClient
{
    private $httpClient;
    private $publicKey;
    private $privateKey;

    public function __construct(Guzzle $httpClient, string $publicKey, string $privateKey)
    {
        $this->httpClient = $httpClient;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    protected function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options = array_merge(['auth' => [$this->publicKey, $this->privateKey]], $options);

        return $this->httpClient->request($method, $uri, $options);
    }
}
