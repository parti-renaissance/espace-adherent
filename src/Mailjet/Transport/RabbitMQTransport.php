<?php

namespace AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\EmailTemplate;
use AppBundle\Producer\MailjetProducerInterface;

class RabbitMQTransport implements TransportInterface
{
    private $producer;

    public function __construct(MailjetProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function sendTemplateEmail(EmailTemplate $email): void
    {
        $this->producer->scheduleEmail($email);
    }
}
