<?php

namespace Tests\App\Test\Mailer;

use App\Mailer\EmailClientInterface;
use App\Mailer\EmailTemplateInterface;
use App\Mailer\Exception\MailerException;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class DummyEmailClient implements EmailClientInterface
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function sendEmail(string $email): string
    {
        $response = $this->httpClient->request('POST', 'send', [
            'body' => $email,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException('Unable to send email to recipients.');
        }

        return (string) $response->getBody();
    }

    public function renderEmail(EmailTemplateInterface $email): string
    {
        return '<h1>Email content</h1>';
    }
}
