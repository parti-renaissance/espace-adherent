<?php

declare(strict_types=1);

namespace App\Recaptcha;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FriendlyCaptchaApiClient implements RecaptchaApiClientInterface
{
    public const NAME = 'friendly_captcha';

    private const VERIFY_URL = '/api/v1/siteverify';

    private HttpClientInterface $client;
    private string $privateKey;
    private string $defaultSiteKey;

    public function __construct(HttpClientInterface $friendlyCaptchaClient, string $privateKey, string $defaultSiteKey)
    {
        $this->client = $friendlyCaptchaClient;
        $this->privateKey = $privateKey;
        $this->defaultSiteKey = $defaultSiteKey;
    }

    public function supports(string $name): bool
    {
        return self::NAME === $name;
    }

    public function verify(string $token, ?string $siteKey): bool
    {
        $response = $this->client->request(Request::METHOD_POST, self::VERIFY_URL, [
            'json' => [
                'solution' => $token,
                'secret' => $this->privateKey,
                'siteKey' => $siteKey ?? $this->defaultSiteKey,
            ],
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            return false;
        }

        $data = $response->toArray(false);

        return $data['success'] ?? false;
    }
}
