<?php

namespace AppBundle\Producer;

use AppBundle\Mailjet\MailjetTemplateEmail;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class MailjetProducer extends Producer
{
    public function scheduleEmail(MailjetTemplateEmail $email): void
    {
        $this->publish(json_encode([
            'email' => $email->getUuid(),
        ]));
    }
}
