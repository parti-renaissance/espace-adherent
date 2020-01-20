<?php

namespace AppBundle\Producer;

use AppBundle\Mailer\AbstractEmailTemplate;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class MailerProducer extends Producer implements MailerProducerInterface
{
    public function scheduleEmail(AbstractEmailTemplate $email): void
    {
        $this->publish(json_encode([
            'uuid' => $email->getUuid()->toString(),
        ]));
    }
}
