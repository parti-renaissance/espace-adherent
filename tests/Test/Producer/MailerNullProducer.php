<?php

namespace Tests\App\Test\Producer;

use App\Mailer\AbstractEmailTemplate;
use App\Producer\MailerProducerInterface;

class MailerNullProducer implements MailerProducerInterface
{
    public function scheduleEmail(AbstractEmailTemplate $mail): void
    {
    }
}
