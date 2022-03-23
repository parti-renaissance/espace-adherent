<?php

namespace App\Recaptcha;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FriendlyCaptchaApiClient implements RecaptchaApiClientInterface
{
    public const NAME = 'friendly_captcha';

    private HttpClientInterface $client;
    private string $privateKey;
    private string $defaultSiteKey;

    public function __construct(HttpClientInterface $client, string $privateKey, string $defaultSiteKey)
    {
        $this->client = $client;
        $this->privateKey = $privateKey;
        $this->defaultSiteKey = $defaultSiteKey;
    }

    public function supports(string $name): bool
    {
        return self::NAME === $name;
    }

    public function verify(string $token, ?string $siteKey): bool
    {
        $response = $this->client->request(Request::METHOD_POST, '/verify', [
            'solution' => $token,
            'secret' => $this->privateKey,
            'siteKey' => $siteKey ?? $this->defaultSiteKey,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            return false;
        }

        $data = $response->toArray();

        return $data['success'] ?? false;
    }
}
