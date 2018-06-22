<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\AbstractEmailClient;
use AppBundle\Mailer\EmailClientInterface;

class EmailClient extends AbstractEmailClient implements EmailClientInterface
{
    public function sendEmail(string $email): string
    {
        $response = $this->request('POST', 'send', [
            'body' => $email,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return (string) $response->getBody();
    }
}
