<?php

namespace App\Adherent\Contribution;

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
            'workflow.contribution_request.completed' => 'manageCommandStore',
        ];
    }

    public function manageCommandStore(Event $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof ContributionRequest) {
            return;
        }

        if (\in_array($event->getTransition()->getName(), [
            ContributionRequestStateEnum::TO_CONTRIBUTION_COMPLETE,
            ContributionRequestStateEnum::TO_NO_CONTRIBUTION_NEEDED,
        ], true)) {
            $this->storage->clear();
        } else {
            $this->storage->save($command);
        }
    }
}
