<?php

namespace AppBundle\Producer;

use AppBundle\Mailjet\MailjetTemplateEmail;
use AppBundle\Mailjet\Message\MailjetMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class MailjetProducer extends Producer implements MailjetProducerInterface
{
    public function scheduleMessage(MailjetMessage $message): void
    {
        $this->publish(json_encode([
            'uuid' => $message->getUuid()->toString(),
        ]));
    }

    public function scheduleEmail(MailjetTemplateEmail $email): void
    {
        $this->publish(json_encode([
            'uuid' => $email->getUuid()->toString(),
        ]));
    }
}
