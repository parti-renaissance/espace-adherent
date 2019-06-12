<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Newsletter\NewsletterSubscriptionHandler;

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
