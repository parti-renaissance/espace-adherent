<?php

namespace App\VotingPlatform\Listener;

use App\Adherent\Certification\Events;
use App\Membership\AdherentEvent;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\VotersListManager;
use App\VotingPlatform\Security\ElectionAuthorisationChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddNewVoterAfterCertificationListener implements EventSubscriberInterface
{
    private $authorisationChecker;
    private $votersListManager;
    private $electionRepository;
    private $entityManager;

    public function __construct(
        ElectionAuthorisationChecker $authorisationChecker,
        VotersListManager $votersListManager,
        ElectionRepository $electionRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->authorisationChecker = $authorisationChecker;
        $this->votersListManager = $votersListManager;
        $this->electionRepository = $electionRepository;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::ADHERENT_CERTIFIED => 'onAdherentCertifiedChange',
        ];
    }

    public function onAdherentCertifiedChange(AdherentEvent $event): void
    {
        $voter = null;
        $count = 0;

        foreach ($event->getAdherent()->getMemberships() as $membership) {
            $committee = $membership->getCommittee();

            if (!$committee->hasActiveElection()) {
                continue;
            }

            $election = $committee->getCurrentElection();

            if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === !$election->getDesignationType()) {
                continue;
            }

            if (!$this->authorisationChecker->canVoteOnCommittee($committee, $membership->getAdherent())) {
                continue;
            }

            if (!$votingPlatformElection = $this->electionRepository->findOneForCommittee($committee, $committee->getCurrentDesignation())) {
                continue;
            }

            $voter = $this->votersListManager->addToElection($votingPlatformElection, $membership->getAdherent());

            ++$count;
        }

        if ($count > 1 && $voter) {
            $voter->setIsGhost(true);
            $this->entityManager->flush();
        }
    }
}
