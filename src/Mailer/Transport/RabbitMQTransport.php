<?php

namespace App\Mailer\Transport;

use App\Mailer\AbstractEmailTemplate;
use App\Producer\MailerProducerInterface;

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
