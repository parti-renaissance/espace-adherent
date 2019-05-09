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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssessorManager
{
    private $assessorRequestRepository;
    private $votePlaceRepository;
    private $manager;
    private $dispatcher;

    public function __construct(
        AssessorRequestRepository $assessorRequestRepository,
        VotePlaceRepository $votePlaceRepository,
        EntityManagerInterface $manager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->assessorRequestRepository = $assessorRequestRepository;
        $this->votePlaceRepository = $votePlaceRepository;
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    public function processAssessorRequest(AssessorRequest $assessorRequest, VotePlace $votePlace = null): void
    {
        $assessorRequest->process($votePlace);

        $this->manager->flush();

        $this->dispatcher->dispatch(
            AssessorRequestEnum::REQUEST_ASSOCIATED,
            new AssessorRequestEvent($assessorRequest)
        );
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
