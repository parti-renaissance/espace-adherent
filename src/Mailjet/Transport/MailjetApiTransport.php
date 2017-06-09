<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\MailjetClient;
use AppBundle\Mailjet\MailjetTemplateEmail;

class MailjetApiTransport implements MailjetMessageTransportInterface
{
    private $client;

    public function __construct(MailjetClient $client)
    {
        $this->client = $client;
    }

    public function sendTemplateEmail(MailjetTemplateEmail $email)
    {
        $response = $this->client->sendEmail(json_encode($email));

        $email->delivered($response);
    }
}
