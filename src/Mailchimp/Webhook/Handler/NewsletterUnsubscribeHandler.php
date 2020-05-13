<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Webhook\EventTypeEnum;
use App\Newsletter\NewsletterSubscriptionHandler;

class NewsletterUnsubscribeHandler implements WebhookHandlerInterface
{
    private $newsletterHandler;
    private $mailchimpObjectIdMapping;

    public function __construct(
        NewsletterSubscriptionHandler $handler,
        MailchimpObjectIdMapping $mailchimpObjectIdMapping
    ) {
        $this->newsletterHandler = $handler;
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    public function handle(array $data): void
    {
        $this->newsletterHandler->unsubscribe($data['email']);
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UNSUBSCRIBE === $type && $listId === $this->mailchimpObjectIdMapping->getNewsletterListId();
    }
}
