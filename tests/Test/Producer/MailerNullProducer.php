<?php

namespace Tests\AppBundle\Test\Producer;

use AppBundle\Mailer\AbstractEmailTemplate;
use AppBundle\Producer\MailerProducerInterface;

class MailerNullProducer implements MailerProducerInterface
{
    public function scheduleEmail(AbstractEmailTemplate $mail): void
    {
    }
}
