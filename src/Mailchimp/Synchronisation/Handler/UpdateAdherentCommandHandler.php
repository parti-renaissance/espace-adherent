<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Synchronisation\Command\UpdateAdherentCommand;
use App\Mailchimp\Webhook\EventTypeEnum;
use App\Mailchimp\Webhook\WebhookHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateAdherentCommandHandler implements MessageHandlerInterface
{
    private $webhookHandler;
    private $mailchimpObjectIdMapping;

    public function __construct(WebhookHandler $webhookHandler, MailchimpObjectIdMapping $mailchimpObjectIdMapping)
    {
        $this->webhookHandler = $webhookHandler;
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    public function __invoke(UpdateAdherentCommand $command): void
    {
        if ($command->isUnsubscribe()) {
            $this->webhookHandler->handle(
                EventTypeEnum::UNSUBSCRIBE,
                $this->mailchimpObjectIdMapping->getMainListId(),
                ['email' => $command->getMail()]
            );
        } else {
            $this->webhookHandler->handle(
                EventTypeEnum::UPDATE_PROFILE,
                $this->mailchimpObjectIdMapping->getMainListId(),
                [
                    'email' => $command->getMail(),
                    'merges' => [
                        'GROUPINGS' => [
                            [
                                'unique_id' => $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId(),
                                'groups' => $command->getSubscriptionTypeLabels(),
                            ],
                            [
                                'unique_id' => $this->mailchimpObjectIdMapping->getMemberInterestInterestGroupId(),
                                'groups' => $command->getInterestLabels(),
                            ],
                        ],
                    ],
                ]
            );
        }
    }
}
