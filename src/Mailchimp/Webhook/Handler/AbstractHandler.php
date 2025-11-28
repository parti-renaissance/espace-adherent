<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractHandler implements WebhookHandlerInterface
{
    protected MailchimpObjectIdMapping $mailchimpObjectIdMapping;
    protected EntityManagerInterface $entityManager;

    #[Required]
    public function setMailchimpObjectIdMapping(MailchimpObjectIdMapping $mailchimpObjectIdMapping): void
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
