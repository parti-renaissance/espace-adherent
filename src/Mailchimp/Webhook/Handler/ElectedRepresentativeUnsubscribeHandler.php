<?php

namespace App\Mailchimp\Webhook\Handler;

use App\ElectedRepresentative\ElectedRepresentativeSubscriptionHandler;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Webhook\EventTypeEnum;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;

class ElectedRepresentativeUnsubscribeHandler implements WebhookHandlerInterface
{
    private $subscriptionHandler;
    private $mailchimpObjectIdMapping;
    private $repository;

    public function __construct(
        ElectedRepresentativeSubscriptionHandler $subscriptionHandler,
        MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        ElectedRepresentativeRepository $repository
    ) {
        $this->subscriptionHandler = $subscriptionHandler;
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
        $this->repository = $repository;
    }

    public function handle(array $data): void
    {
        foreach ($this->findElectedRepresentatives($data['email']) as $electedRepresentative) {
            $this->subscriptionHandler->unsubscribe($electedRepresentative);
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UNSUBSCRIBE === $type && $listId === $this->mailchimpObjectIdMapping->getElectedRepresentativeListId();
    }

    private function findElectedRepresentatives(string $email): array
    {
        return $this->repository->findByEmail($email);
    }
}
