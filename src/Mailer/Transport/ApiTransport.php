<?php

namespace AppBundle\Mailer\Transport;

use AppBundle\Mailer\EmailClientInterface;
use AppBundle\Mailer\EmailTemplate;

class ApiTransport implements TransportInterface
{
    private $client;

    public function __construct(EmailClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendTemplateEmail(EmailTemplate $email): void
    {
        $email->delivered($this->client->sendEmail(json_encode($email)));
    }
}
