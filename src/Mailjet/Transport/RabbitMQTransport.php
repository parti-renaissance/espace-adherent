<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\MailjetTemplateEmail;
use AppBundle\Producer\MailjetProducerInterface;

class RabbitMQTransport implements MailjetMessageTransportInterface
{
    private $producer;

    public function __construct(MailjetProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function sendTemplateEmail(MailjetTemplateEmail $email): void
    {
        $this->producer->scheduleEmail($email);
    }
}
