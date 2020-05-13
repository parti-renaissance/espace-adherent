<?php

namespace Tests\App\Test\Mailer;

use App\Mailer\AbstractEmailClient;
use App\Mailer\EmailClientInterface;
use App\Mailer\Exception\MailerException;
use Symfony\Component\HttpFoundation\Response;

class DummyEmailClient extends AbstractEmailClient implements EmailClientInterface
{
    public function sendEmail(string $email): string
    {
        $response = $this->request('POST', 'send', [
            'body' => $email,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException('Unable to send email to recipients.');
        }

        return (string) $response->getBody();
    }
}
