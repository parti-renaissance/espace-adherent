<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;

abstract class AbstractElectedRepresentativeHandler implements WebhookHandlerInterface
{
    /** @var ElectedRepresentativeRepository */
    private $repository;

    /** @var MailchimpObjectIdMapping */
    protected $mailchimpObjectIdMapping;

    /**
     * @required
     */
    public function setRepository(ElectedRepresentativeRepository $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @required
     */
    public function setMailchimpObjectIdMapping(MailchimpObjectIdMapping $mailchimpObjectIdMapping): void
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    public function support(string $type, string $listId): bool
    {
        return $listId === $this->mailchimpObjectIdMapping->getElectedRepresentativeListId();
    }

    protected function findElectedRepresentatives(string $email): array
    {
        return $this->repository->findByEmail($email);
    }
}
