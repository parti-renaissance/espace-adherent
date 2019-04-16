<?php

namespace AppBundle\Assessor;

use AppBundle\Assessor\Filter\AssessorRequestFilters;
use AppBundle\Assessor\Filter\VotePlaceFilters;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use AppBundle\Repository\AssessorRequestRepository;
use AppBundle\Repository\VotePlaceRepository;
use Doctrine\ORM\EntityManagerInterface;

class AssessorManager
{
    private $assessorRequestRepository;
    private $votePlaceRepository;
    private $manager;

    public function __construct(
        AssessorRequestRepository $assessorRequestRepository,
        VotePlaceRepository $votePlaceRepository,
        EntityManagerInterface $manager
    ) {
        $this->assessorRequestRepository = $assessorRequestRepository;
        $this->votePlaceRepository = $votePlaceRepository;
        $this->manager = $manager;
    }

    public function processAssessorRequest(AssessorRequest $assessorRequest, VotePlace $votePlace = null): void
    {
        $assessorRequest->process($votePlace);

        $this->manager->flush();
    }

    public function unprocessAssessorRequest(AssessorRequest $assessorRequest): void
    {
        $assessorRequest->unprocess();

        $this->manager->flush();
    }

    public function enableAssessorRequest(AssessorRequest $assessorRequest): void
    {
        $assessorRequest->enable();

        $this->manager->flush();
    }

    public function disableAssessorRequest(AssessorRequest $assessorRequest): void
    {
        $assessorRequest->disable();

        $this->manager->flush();
    }

    public function getAssessorRequests(Adherent $manager, AssessorRequestFilters $filters): array
    {
        return $this->assessorRequestRepository->findMatchingRequests($manager, $filters);
    }

    public function countAssessorRequests(Adherent $manager, AssessorRequestFilters $filters): int
    {
        return $this->assessorRequestRepository->countMatchingRequests($manager, $filters);
    }

    public function getVotePlacesProposals(Adherent $manager, VotePlaceFilters $filters): array
    {
        return $this->votePlaceRepository->findMatchingProposals($manager, $filters);
    }

    public function countVotePlacesProposals(Adherent $manager, VotePlaceFilters $filters)
    {
        return $this->votePlaceRepository->countMatchingProposals($manager, $filters);
    }
}
