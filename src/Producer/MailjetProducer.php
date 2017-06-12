<?php

namespace AppBundle\Producer;

use AppBundle\Mailjet\EmailTemplate;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class MailjetProducer extends Producer implements MailjetProducerInterface
{
    public function scheduleEmail(EmailTemplate $email): void
    {
        $this->publish(json_encode([
            'uuid' => $email->getUuid()->toString(),
        ]));
    }
}
