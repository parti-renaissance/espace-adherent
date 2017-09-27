<?php

namespace AppBundle\Producer;

use AppBundle\Mailjet\EmailTemplate;

interface MailjetProducerInterface
{
    public function scheduleEmail(EmailTemplate $mail): void;
}
