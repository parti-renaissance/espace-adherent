<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\EmailClientInterface;
use AppBundle\Mailjet\Exception\MailjetException;

class EmailClient extends MailjetClient implements EmailClientInterface
{
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
