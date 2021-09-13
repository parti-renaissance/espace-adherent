<?php

namespace App\OvhCloud;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Driver
{
    private const HOST = 'eu.api.ovh.com';

    private $httpClient;
    private $serviceName;
    private $applicationKey;
    private $applicationSecret;
    private $consumerKey;

    public function __construct(
        HttpClientInterface $httpClient,
        string $applicationKey,
        string $applicationSecret,
        string $consumerKey,
        string $serviceName
    ) {
        $this->httpClient = $httpClient;

        $this->applicationKey = $applicationKey;
        $this->applicationSecret = $applicationSecret;
        $this->consumerKey = $consumerKey;
        $this->serviceName = $serviceName;
    }

    public function sendSmsBatch(string $message, string $name, array $phones): ResponseInterface
    {
        $content = [
            'name' => $name,
            'message' => $message,
            'to' => $phones,
            'from' => 'En Marche',
            'noStop' => false,
        ];

        $url = sprintf('https://%s/1.0/sms/%s/batches', self::HOST, $this->serviceName);

        return $this->send('POST', $url, $content);
    }

    private function send(string $method, string $url, array $content): ResponseInterface
    {
        $headers = [];

        $now = time() + $this->calculateTimeDelta();
        $headers['X-Ovh-Application'] = $this->applicationKey;
        $headers['X-Ovh-Timestamp'] = $now;
        $headers['Content-Type'] = 'application/json';

        $body = json_encode($content, \JSON_UNESCAPED_SLASHES);

        $toSign = [$this->applicationSecret, $this->consumerKey, $method, $url, $body, $now];
        $headers['X-Ovh-Consumer'] = $this->consumerKey;
        $headers['X-Ovh-Signature'] = '$1$'.sha1(implode('+', $toSign));

        $options = [
            'headers' => $headers,
            'body' => $body,
        ];

        return $this->httpClient->request($method, $url, $options);
    }

    /**
     * Calculates the time delta between the local machine and the API server.
     */
    private function calculateTimeDelta(): int
    {
        $response = $this->httpClient->request('GET', sprintf('https://%s/1.0/auth/time', self::HOST));

        return $response->getContent() - time();
    }
}
