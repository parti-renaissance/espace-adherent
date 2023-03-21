<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractHandler implements WebhookHandlerInterface
{
    protected MailchimpObjectIdMapping $mailchimpObjectIdMapping;
    protected EntityManagerInterface $entityManager;

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setMailchimpObjectIdMapping(MailchimpObjectIdMapping $mailchimpObjectIdMapping): void
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
