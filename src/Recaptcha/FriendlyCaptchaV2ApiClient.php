<?php

declare(strict_types=1);

namespace App\Recaptcha;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FriendlyCaptchaV2ApiClient implements RecaptchaApiClientInterface
{
    public const NAME = 'friendly_captcha_v2';

    private const VERIFY_URL = '/api/v2/captcha/siteverify';

    private HttpClientInterface $client;
    private string $apiKey;
    private string $defaultSiteKey;

    public function __construct(HttpClientInterface $friendlyCaptchaV2Client, string $apiKey, string $defaultSiteKey)
    {
        $this->client = $friendlyCaptchaV2Client;
        $this->apiKey = $apiKey;
        $this->defaultSiteKey = $defaultSiteKey;
    }

    public function supports(string $name): bool
    {
        return self::NAME === $name;
    }

    public function verify(string $token, ?string $siteKey): bool
    {
        $response = $this->client->request(Request::METHOD_POST, self::VERIFY_URL, [
            'headers' => [
                'X-API-Key' => $this->apiKey,
            ],
            'json' => [
                'response' => $token,
                'sitekey' => $siteKey ?? $this->defaultSiteKey,
            ],
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            return false;
        }

        $data = $response->toArray(false);

        return true === ($data['success'] ?? false);
    }
}
