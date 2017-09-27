<?php

namespace AppBundle\Mailer\Transport;

use AppBundle\Mailer\EmailTemplate;
use AppBundle\Producer\MailerProducerInterface;

class RabbitMQTransport implements TransportInterface
{
    private $producer;

    public function __construct(MailerProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function sendTemplateEmail(EmailTemplate $email): void
    {
        $this->producer->scheduleEmail($email);
    }
}
