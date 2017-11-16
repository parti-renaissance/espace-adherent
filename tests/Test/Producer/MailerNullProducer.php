<?php

namespace Tests\AppBundle\Test\Producer;

use AppBundle\Mailer\EmailTemplate;
use AppBundle\Producer\MailerProducerInterface;

class MailerNullProducer implements MailerProducerInterface
{
    public function scheduleEmail(EmailTemplate $mail): void
    {
    }
}
