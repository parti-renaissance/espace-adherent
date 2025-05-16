<?php

namespace App\Adherent\Listener;

use App\Address\AddressInterface;
use App\Committee\CommitteeManager;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\PostAddress;
use App\History\AdministratorActionEvent;
use App\History\AdministratorActionEvents;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\VotingPlatform\VoterRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssignCommitteeByAdherentAddressListener implements EventSubscriberInterface
{
    private ?PostAddress $beforeAddress = null;

    public function __construct(
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly CommitteeManager $committeeManager,
        private readonly VoterRepository $voterRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdministratorActionEvents::ADMIN_USER_PROFILE_BEFORE_UPDATE => 'onBeforeUpdateFromAdmin',
            UserEvents::USER_UPDATED => 'onAfterUpdate',
            UserEvents::USER_VALIDATED => 'onAfterUpdate',
        ];
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $this->beforeAddress = clone $event->getAdherent()->getPostAddress();
    }

    public function onBeforeUpdateFromAdmin(AdministratorActionEvent $event): void
    {
        $this->beforeAddress = clone $event->adherent->getPostAddress();
    }

    public function onAfterUpdate(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        if (!$adherent->isEnabled()) {
            return;
        }

        $address = $adherent->getPostAddress();
        $isAddressChanged = !$this->beforeAddress || !$this->beforeAddress->equals($address);

        $isDptChanged = $this->isDepartmentChanged($this->beforeAddress, $address);

        $currentCommitteeMembership = $adherent->getCommitteeMembership();
        $currentCommittee = $currentCommitteeMembership?->getCommittee();

        if (!$isAddressChanged && null !== $currentCommittee) {
            return;
        }

        if (!$committeeByAddress = $this->committeeManager->findCommitteeByAddress($address)) {
            return;
        }

        if ($isDptChanged || !$this->voterRepository->isInVoterListForCommitteeElection($adherent)) {
            $this->committeeMembershipManager->followCommittee($adherent, $committeeByAddress, CommitteeMembershipTriggerEnum::ADDRESS_UPDATE);
        }
    }

    private function isDepartmentChanged(?PostAddress $beforeAddress, PostAddress $address): bool
    {
        if (!$beforeAddress) {
            return true;
        }

        if ($beforeAddress->getCountry() !== $address->getCountry()) {
            return true;
        }

        if (AddressInterface::FRANCE !== $beforeAddress->getCountry()) {
            return false;
        }

        return mb_substr($beforeAddress->getPostalCode() ?? '', 0, 2) !== mb_substr($address->getPostalCode() ?? '', 0, 2);
    }
}
