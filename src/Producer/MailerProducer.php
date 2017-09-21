<?php

namespace AppBundle\Producer;

use AppBundle\Mailer\EmailTemplate;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class MailerProducer extends Producer implements MailerProducerInterface
{
    public function scheduleEmail(EmailTemplate $email): void
    {
        $this->publish(json_encode([
            'uuid' => $email->getUuid()->toString(),
        ]));
    }
}
