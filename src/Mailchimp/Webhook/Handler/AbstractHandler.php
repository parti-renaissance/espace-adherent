<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractHandler implements WebhookHandlerInterface
{
    protected MailchimpObjectIdMapping $mailchimpObjectIdMapping;
    protected EntityManagerInterface $entityManager;

    /** @required */
    public function setMailchimpObjectIdMapping(MailchimpObjectIdMapping $mailchimpObjectIdMapping): void
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
