<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\ClientInterface;
use AppBundle\Mailjet\EmailTemplate;

class ApiTransport implements TransportInterface
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendTemplateEmail(EmailTemplate $email): void
    {
        $email->delivered($this->client->sendEmail(json_encode($email)));
    }
}
