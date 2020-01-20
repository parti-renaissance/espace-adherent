<?php

namespace AppBundle\Mailer\Transport;

use AppBundle\Mailer\AbstractEmailTemplate;
use AppBundle\Mailer\EmailClientInterface;

class ApiTransport implements TransportInterface
{
    private $client;

    public function __construct(EmailClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendTemplateEmail(AbstractEmailTemplate $email): void
    {
        $email->delivered($this->client->sendEmail(json_encode($email)));
    }
}
