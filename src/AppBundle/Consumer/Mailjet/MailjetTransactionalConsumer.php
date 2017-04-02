<?php

namespace AppBundle\Consumer\Mailjet;

use AppBundle\Mailjet\MailjetService;

class MailjetTransactionalConsumer extends AbstractMailjetConsumer
{
    protected function getName(): string
    {
        return 'mailjet-transactional';
    }

    protected function getMailjet(): MailjetService
    {
        return $this->container->get('app.mailjet.client.transactional');
    }
}
