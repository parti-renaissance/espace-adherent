<?php

namespace App\Adherent\Listener;

use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\PostAddress;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssignCommitteeByAdherentAddressListener implements EventSubscriberInterface
{
    private ?PostAddress $beforeAddress = null;

    public function __construct(private readonly CommitteeMembershipManager $committeeManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_BEFORE_UPDATE => 'onBeforeUpdate',
            UserEvents::USER_UPDATED => 'onAfterUpdate',
        ];
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $this->beforeAddress = clone $event->getUser()->getPostAddress();
    }

    public function onAfterUpdate(UserEvent $event): void
    {
        $adherent = $event->getUser();
        $address = $adherent->getPostAddress();
        $isAddressChanged = !$this->beforeAddress || !$this->beforeAddress->equals($address);

        $currentCommitteeMembership = $adherent->getCommitteeV2Membership();
        $currentCommittee = $currentCommitteeMembership?->getCommittee();

        if (!$isAddressChanged && null !== $currentCommittee) {
            return;
        }

        if (!$committeeByAddress = $this->committeeManager->findCommitteeByAddress($address)) {
            return;
        }

        $this->committeeManager->followCommittee($adherent, $committeeByAddress, CommitteeMembershipTriggerEnum::ADDRESS_UPDATE);
    }
}
