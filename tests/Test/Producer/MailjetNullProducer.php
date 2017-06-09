<?php

namespace Tests\AppBundle\Test\Producer;

use AppBundle\Mailjet\MailjetTemplateEmail;
use AppBundle\Mailjet\Message\MailjetMessage;
use AppBundle\Producer\MailjetProducerInterface;

class MailjetNullProducer implements MailjetProducerInterface
{
    public function scheduleMessage(MailjetMessage $message): void
    {
    }

    public function scheduleEmail(MailjetTemplateEmail $mail): void
    {
    }
}
