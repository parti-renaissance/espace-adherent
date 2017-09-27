<?php

namespace AppBundle\Producer;

use AppBundle\Mailer\EmailTemplate;

interface MailerProducerInterface
{
    public function scheduleEmail(EmailTemplate $mail): void;
}
