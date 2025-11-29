<?php

declare(strict_types=1);

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
            'workflow.contribution_process.completed' => 'manageCommandStore',
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
