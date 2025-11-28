<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook;

use App\Mailchimp\Webhook\Command\CatchMailchimpWebhookCallCommand;
use App\Mailchimp\Webhook\Exception\MailchimpWebhookException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CatchMailchimpWebhookCallCommandHandler
{
    private WebhookHandler $handler;

    public function __construct(WebhookHandler $handler)
    {
        $this->handler = $handler;
    }

    public function __invoke(CatchMailchimpWebhookCallCommand $command): void
    {
        if (!$type = $command->getType()) {
            throw MailchimpWebhookException::missingWebhookType();
        }

        if (!EventTypeEnum::isValid($type)) {
            throw MailchimpWebhookException::invalidWebhookType($type);
        }

        if (!$listId = $command->getListId()) {
            throw MailchimpWebhookException::missingListId();
        }

        $this->handler->handle($type, $listId, $command->getData());
    }
}
