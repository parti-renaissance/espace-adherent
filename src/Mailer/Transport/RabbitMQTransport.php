<?php

namespace AppBundle\Mailer\Transport;

use AppBundle\Mailer\AbstractEmailTemplate;
use AppBundle\Producer\MailerProducerInterface;

class RabbitMQTransport implements TransportInterface
{
    private $producer;

    public function __construct(MailerProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function sendTemplateEmail(AbstractEmailTemplate $email): void
    {
        $this->producer->scheduleEmail($email);
    }
}
