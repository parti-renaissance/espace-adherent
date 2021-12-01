<?php

namespace App\OvhCloud;

use App\OvhCloud\Exception\ContactNotFoundException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Driver
{
    private const HOST = 'eu.api.ovh.com';

    private HttpClientInterface $httpClient;
    private string $serviceName;
    private string $applicationKey;
    private string $applicationSecret;
    private string $consumerKey;
    private string $senderName;

    public function __construct(
        HttpClientInterface $httpClient,
        string $applicationKey,
        string $applicationSecret,
        string $consumerKey,
        string $serviceName,
        string $senderName
    ) {
        $this->httpClient = $httpClient;

        $this->applicationKey = $applicationKey;
        $this->applicationSecret = $applicationSecret;
        $this->consumerKey = $consumerKey;
        $this->serviceName = $serviceName;
        $this->senderName = $senderName;
    }

    public function getBatchStats(string $batchId): ResponseInterface
    {
        return $this->send('GET', sprintf('https://%s/1.0/sms/%s/batches/%s/statistics', self::HOST, $this->serviceName, $batchId));
    }

    public function resubscribeContact(string $phone): void
    {
        $response = $this->send('DELETE', sprintf('https://%s/1.0/sms/%s/blacklists/%s', self::HOST, $this->serviceName, $phone));

        if (200 !== $code = $response->getStatusCode()) {
            throw new ContactNotFoundException($response->getContent(false), $code);
        }
    }

    public function sendSmsBatch(string $message, string $name, array $phones): ResponseInterface
    {
        $content = [
            'name' => $name,
            'message' => $message,
            'to' => $phones,
            'from' => $this->senderName,
            'noStop' => false,
        ];

        return $this->send('POST', sprintf('https://%s/1.0/sms/%s/batches', self::HOST, $this->serviceName), $content);
    }

    private function send(string $method, string $url, array $content = []): ResponseInterface
    {
        $requestOptions = [];
        $headers = [];

        $now = time() + $this->calculateTimeDelta();
        $headers['X-Ovh-Application'] = $this->applicationKey;
        $headers['X-Ovh-Timestamp'] = $now;
        $headers['Content-Type'] = 'application/json';

        $body = null;
        if ($content) {
            $requestOptions['body'] = $body = json_encode($content, \JSON_UNESCAPED_SLASHES);
        }

        $toSign = [$this->applicationSecret, $this->consumerKey, $method, $url, $body, $now];
        $headers['X-Ovh-Consumer'] = $this->consumerKey;
        $headers['X-Ovh-Signature'] = '$1$'.sha1(implode('+', $toSign));

        $requestOptions['headers'] = $headers;

        return $this->httpClient->request($method, $url, $requestOptions);
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
