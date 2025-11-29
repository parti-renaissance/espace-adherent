<?php

declare(strict_types=1);

namespace Tests\App\Test\Mailer;

use App\Mailer\EmailClientInterface;
use App\Mailer\Exception\MailerException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DummyEmailClient implements EmailClientInterface
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function sendEmail(string $email, bool $resend = false, bool $useTemplateEndpoint = true): string
    {
        $response = $this->httpClient->request('POST', 'send', ['body' => $email]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException('Unable to send email to recipients.');
        }

        return $response->getContent();
    }
}
