<?php

namespace App\Renaissance\Donation\Listener;

use App\Donation\DonationRequest;
use App\Renaissance\Donation\DonationRequestStateEnum;
use App\Renaissance\Donation\DonationRequestStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class DonationRequestStoreListener implements EventSubscriberInterface
{
    private DonationRequestStorage $storage;

    public function __construct(DonationRequestStorage $storage)
    {
        $this->storage = $storage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.donation_request.completed' => 'manageCommandStore',
        ];
    }

    public function manageCommandStore(Event $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof DonationRequest) {
            return;
        }

        if (DonationRequestStateEnum::TO_FINISH === $event->getTransition()->getName()) {
            $this->storage->clear();
        } else {
            $this->storage->save($command);
        }
    }
}
