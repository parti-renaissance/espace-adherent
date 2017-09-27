<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Exception\MailjetException;
use GuzzleHttp\ClientInterface as Guzzle;

class ApiClient implements ClientInterface
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

    public function sendEmail(string $email): string
    {
        $response = $this->httpClient->request('POST', 'send', [
            'auth' => [$this->publicKey, $this->privateKey],
            'body' => $email,
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new MailjetException('Unable to send email to recipients.');
        }

        return (string) $response->getBody();
    }
}
