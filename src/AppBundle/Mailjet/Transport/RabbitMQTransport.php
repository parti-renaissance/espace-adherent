<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\MailjetTemplateEmail;
use AppBundle\Producer\MailjetProducer;

class RabbitMQTransport implements MailjetMessageTransportInterface
{
    private $producer;

    public function __construct(MailjetProducer $producer)
    {
        $this->producer = $producer;
    }

    public function sendTemplateEmail(MailjetTemplateEmail $email): void
    {
        $this->producer->scheduleEmail($email);
    }
}
