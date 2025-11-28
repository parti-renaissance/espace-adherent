<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Newsletter\NewsletterSubscriptionHandler;

class NewsletterUnsubscribeHandler extends AbstractHandler
{
    private NewsletterSubscriptionHandler $newsletterHandler;

    public function __construct(NewsletterSubscriptionHandler $handler)
    {
        $this->newsletterHandler = $handler;
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
