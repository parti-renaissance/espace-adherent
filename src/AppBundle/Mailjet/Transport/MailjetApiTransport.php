<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\MailjetTemplateEmail;
use GuzzleHttp\ClientInterface;

class MailjetApiTransport implements MailjetMessageTransportInterface
{
    private $httpClient;
    private $publicKey;
    private $privateKey;

    public function __construct(
        ClientInterface $httpClient,
        string $publicKey,
        string $privateKey
    ) {
        $this->httpClient = $httpClient;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function sendTemplateEmail(MailjetTemplateEmail $email)
    {
        $response = $this->httpClient->request('POST', 'send', [
            'auth' => [$this->publicKey, $this->privateKey],
            'body' => $email->getHttpRequestPayload(),
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new MailjetException('Unable to send email to recipients.');
        }

        $email->delivered((string) $response->getBody());
    }
}
