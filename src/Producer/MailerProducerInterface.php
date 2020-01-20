<?php

namespace AppBundle\Producer;

use AppBundle\Mailer\AbstractEmailTemplate;

interface MailerProducerInterface
{
    public function scheduleEmail(AbstractEmailTemplate $mail): void;
}
