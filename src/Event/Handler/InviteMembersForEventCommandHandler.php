<?php

declare(strict_types=1);

namespace App\Event\Handler;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\RegistrationStatusEnum;
use App\Event\Command\InviteMembersForEventCommand;
use App\Event\EventInvitationNotifier;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\JeMengage\Push\Command\EventCreationNotificationCommand;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\Event\EventRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class InviteMembersForEventCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly EventRegistrationCommandHandler $registrationHandler,
        private readonly EventInvitationNotifier $notifier,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(InviteMembersForEventCommand $command): void
    {
        if (!$event = $this->eventRepository->findOneByUuid($command->getUuid()->toRfc4122())) {
            return;
        }

        if (!$event->isAnnounceEnabled()) {
            return;
        }

        $candidates = $this->getCandidates($event);

        $invited = [];

        foreach ($candidates as $adherent) {
            if (!$this->isAdherentEligible($adherent)) {
                continue;
            }

            if ($this->registrationHandler->handle(new EventRegistrationCommand($event, $adherent, RegistrationStatusEnum::INVITED), false)) {
                $invited[] = $adherent;
            }
        }

        if ($invited) {
            $this->notifier->sendEventInvitation($event, $invited);

            $this->bus->dispatch(new EventCreationNotificationCommand($event->getUuid()));
        }
    }

    /**
     * @return Adherent[]
     */
    private function getCandidates(Event $event): array
    {
        if ($agora = $event->agora) {
            if (!$agora->published) {
                return [];
            }

            $adherents = [];
            foreach ($agora->memberships as $membership) {
                if ($adherent = $membership->adherent) {
                    $adherents[] = $adherent;
                }
            }

            return $adherents;
        }

        if ($committee = $event->getCommittee()) {
            if (!$committee->isApproved()) {
                return [];
            }

            $adherents = [];
            foreach ($this->committeeMembershipRepository->findCommitteeMemberships($committee) as $membership) {
                if ($adherent = $membership->getAdherent()) {
                    $adherents[] = $adherent;
                }
            }

            return $adherents;
        }

        return [];
    }

    private function isAdherentEligible(Adherent $adherent): bool
    {
        return $adherent->isEnabled() && $adherent->hasTag(TagEnum::ADHERENT);
    }
}
