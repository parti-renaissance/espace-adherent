<?php

namespace AppBundle\Producer\Mailjet;

use AppBundle\Mailjet\Message\MailjetMessage;

interface MailjetMessageProducerInterface
{
    /**
     * Schedule the sending of a message.
     *
     * @param MailjetMessage $message
     */
    public function scheduleMessage(MailjetMessage $message);
}
