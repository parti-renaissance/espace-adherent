<?php

namespace App\Recaptcha;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaApiClient implements RecaptchaApiClientInterface
{
    private const BASE_URL = 'https://www.google.com/recaptcha/api/';

    private HttpClientInterface $client;
    private string $privateKey;
    private ?RequestStack $requestStack;

    public function __construct(HttpClientInterface $client, string $privateKey, RequestStack $requestStack = null)
    {
        $this->client = $client;

        $this->privateKey = $privateKey;
        $this->requestStack = $requestStack;
    }

    public function verify(string $answer, string $clientIp = null): bool
    {
        $response = $this->client->request('POST', self::BASE_URL.'siteverify', [
            'body' => $this->getParameters($answer, $clientIp),
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Unable to verify captcha answer.');
        }

        $data = $response->toArray();
        if (null === $data || !isset($data['success']) || !\is_bool($data['success'])) {
            throw new \RuntimeException('Unexpected JSON response.');
        }

        return $data['success'];
    }

    private function getParameters(string $answer, string $clientIp = null): array
    {
        $params = [
            'secret' => $this->privateKey,
            'response' => $answer,
        ];

        if (null === $clientIp) {
            $clientIp = $this->getClientIp();
        }

        if ($clientIp) {
            $params['remoteip'] = $clientIp;
        }

        return $params;
    }

    private function getRequest(): ?Request
    {
        return $this->requestStack ? $this->requestStack->getMasterRequest() : null;
    }

    private function getClientIp(): ?string
    {
        if ($request = $this->getRequest()) {
            return $request->getClientIp();
        }

        return null;
    }
}
