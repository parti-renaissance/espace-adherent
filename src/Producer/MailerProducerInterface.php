<?php

namespace App\Producer;

use App\Mailer\AbstractEmailTemplate;

interface MailerProducerInterface
{
    public function scheduleEmail(AbstractEmailTemplate $mail): void;
}
