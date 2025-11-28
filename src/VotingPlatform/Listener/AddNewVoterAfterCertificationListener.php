<?php

declare(strict_types=1);

namespace App\VotingPlatform\Listener;

use App\Adherent\Certification\Events;
use App\Membership\Event\UserEvent;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\VotersListManager;
use App\VotingPlatform\Security\ElectionAuthorisationChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddNewVoterAfterCertificationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ElectionAuthorisationChecker $authorisationChecker,
        private readonly VotersListManager $votersListManager,
        private readonly ElectionRepository $electionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ADHERENT_CERTIFIED => 'onAdherentCertifiedChange',
        ];
    }

    public function onAdherentCertifiedChange(UserEvent $event): void
    {
        if (!$membership = $event->getAdherent()->getCommitteeMembership()) {
            return;
        }

        $committee = $membership->getCommittee();

        if (!$committee->hasActiveElection()) {
            return;
        }

        $election = $committee->getCurrentElection();

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR !== $election->getDesignationType()) {
            return;
        }

        if (!$this->authorisationChecker->canVoteOnCommittee($committee, $membership->getAdherent())) {
            return;
        }

        if (!$votingPlatformElection = $this->electionRepository->findOneForCommittee($committee, $committee->getCurrentDesignation())) {
            return;
        }

        if ($voter = $this->votersListManager->addToElection($votingPlatformElection, $membership->getAdherent())) {
            $voter->setIsGhost(true);
            $this->entityManager->flush();
        }
    }
}
