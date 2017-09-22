<?php

namespace AppBundle\Mailjet;

use GuzzleHttp\ClientInterface as Guzzle;
use Psr\Http\Message\ResponseInterface;

abstract class MailjetClient
{
    protected $httpClient;
    protected $publicKey;
    protected $privateKey;

    public function __construct(Guzzle $httpClient, string $publicKey, string $privateKey)
    {
        $this->httpClient = $httpClient;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    protected function getBody(ResponseInterface $response): array
    {
        return json_decode($response->getBody(), true);
    }
}
