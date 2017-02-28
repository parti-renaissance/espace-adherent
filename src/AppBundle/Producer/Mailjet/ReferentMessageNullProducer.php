<?php

namespace AppBundle\Producer\Mailjet;

use AppBundle\Mailjet\Message\ReferentMessage;

class ReferentMessageNullProducer implements ReferentMessageProducerInterface
{
    /**
     * Schedule the sending of a referent message.
     *
     * @param ReferentMessage $message
     */
    public function scheduleMessage(ReferentMessage $message)
    {
    }
}
