<?php

namespace AppBundle\Producer\Mailjet;

use AppBundle\Mailjet\Message\MailjetMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class MailjetMessageProducer extends Producer implements MailjetMessageProducerInterface
{
    /**
     * {@inheritdoc}
     */
    public function scheduleMessage(MailjetMessage $message)
    {
        $this->publish(json_encode([
            'uuid' => $message->getUuid(),
            'message' => serialize($message),
        ]));
    }
}
