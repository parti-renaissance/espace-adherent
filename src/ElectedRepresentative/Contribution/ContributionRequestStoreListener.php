<?php

namespace App\ElectedRepresentative\Contribution;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class ContributionRequestStoreListener implements EventSubscriberInterface
{
    public function __construct(private readonly ContributionRequestStorage $storage)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.elected_representative_contribution_request.completed' => 'manageCommandStore',
        ];
    }

    public function manageCommandStore(Event $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof ContributionRequest) {
            return;
        }

        if (ContributionRequestStateEnum::TO_FINISH === $event->getTransition()->getName()) {
            $this->storage->clear();
        } else {
            $this->storage->save($command);
        }
    }
}
