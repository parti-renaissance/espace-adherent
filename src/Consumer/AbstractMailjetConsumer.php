<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\MailjetEmail;

abstract class AbstractMailjetConsumer extends AbstractMailerConsumer
{
    protected function getEmailEntityClass(): string
    {
        return MailjetEmail::class;
    }
}
