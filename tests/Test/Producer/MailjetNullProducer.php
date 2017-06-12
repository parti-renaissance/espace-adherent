<?php

namespace Tests\AppBundle\Test\Producer;

use AppBundle\Mailjet\EmailTemplate;
use AppBundle\Producer\MailjetProducerInterface;

class MailjetNullProducer implements MailjetProducerInterface
{
    public function scheduleEmail(EmailTemplate $mail): void
    {
    }
}
