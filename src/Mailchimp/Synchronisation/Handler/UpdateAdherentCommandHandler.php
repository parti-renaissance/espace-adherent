<?php

namespace AppBundle\Mailchimp\Synchronisation\Handler;

use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\Synchronisation\Command\UpdateAdherentCommand;
use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Mailchimp\Webhook\WebhookHandler;
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
            $this->webhookHandler->handle(EventTypeEnum::UNSUBSCRIBE, ['email' => $command->getMail()]);
        } else {
            $this->webhookHandler->handle(
                EventTypeEnum::UPDATE_PROFILE,
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
