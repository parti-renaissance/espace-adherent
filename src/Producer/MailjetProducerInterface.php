<?php

namespace AppBundle\Producer;

use AppBundle\Mailjet\MailjetTemplateEmail;
use AppBundle\Mailjet\Message\MailjetMessage;

interface MailjetProducerInterface
{
    public function scheduleMessage(MailjetMessage $message): void;

    public function scheduleEmail(MailjetTemplateEmail $mail): void;
}
