<?php

namespace App\Recaptcha;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class RecaptchaApiClient extends Client
{
    private $privateKey;
    private $requestStack;

    public function __construct(string $privateKey, array $config = [], RequestStack $requestStack = null)
    {
        if (empty($config['base_uri'])) {
            $config['base_uri'] = 'https://www.google.com/recaptcha/api/';
        }

        parent::__construct($config);

        $this->privateKey = $privateKey;
        $this->requestStack = $requestStack;
    }

    public function verify(string $answer, string $clientIp = null): bool
    {
        $response = $this->post('siteverify', [
            'form_params' => $this->getParameters($answer, $clientIp),
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('Unable to verify captcha answer.');
        }

        $data = json_decode((string) $response->getBody(), true);
        if (null === $data || !isset($data['success']) || !\is_bool($data['success'])) {
            throw new \RuntimeException('Unexpected JSON response.');
        }

        return $data['success'];
    }

    private function getParameters(string $answer, string $clientIp = null)
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

    private function getRequest()
    {
        $request = null;
        if ($this->requestStack) {
            $request = $this->requestStack->getMasterRequest();
        }

        return $request;
    }

    private function getClientIp()
    {
        $clientIp = null;
        if ($request = $this->getRequest()) {
            $clientIp = $request->getClientIp();
        }

        return $clientIp;
    }
}
