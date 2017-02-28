<?php

namespace AppBundle\Producer\Mailjet;

use AppBundle\Mailjet\Message\ReferentMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class ReferentMessageMailjetProducer extends Producer implements ReferentMessageProducerInterface
{
    /**
     * Schedule the sending of a referent message.
     *
     * @param ReferentMessage $message
     */
    public function scheduleMessage(ReferentMessage $message)
    {
        $this->publish(json_encode([
            'uuid' => $message->getUuid(),
            'message' => serialize($message),
        ]));
    }
}
