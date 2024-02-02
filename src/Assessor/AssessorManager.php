<?php

namespace App\Assessor;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Assessor\Filter\AssessorRequestFilters;
use App\Assessor\Filter\CitiesFilters;
use App\Assessor\Filter\VotePlaceFilters;
use App\Entity\Adherent;
use App\Entity\AssessorRequest;
use App\Entity\Election\VotePlace;
use App\Repository\AssessorRequestRepository;
use App\Repository\Election\VotePlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    public function processAssessorRequest(AssessorRequest $assessorRequest, ?VotePlace $votePlace = null): void
    {
        $assessorRequest->process($votePlace);

        $this->manager->flush();

        $this->dispatcher->dispatch(new AssessorRequestEvent($assessorRequest), AssessorRequestEnum::REQUEST_ASSOCIATED);
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

    public function getVotePlacesProposals(Adherent $manager, VotePlaceFilters $filters): PaginatorInterface
    {
        return $this->votePlaceRepository->findMatchingProposals($manager, $filters);
    }

    public function getOfficeAvailabilities(array $votePlaceIds): array
    {
        if (empty($votePlaceIds)) {
            return [];
        }

        return $this->votePlaceRepository->getOfficeAvailabilities($votePlaceIds);
    }

    public function getVotePlacesCities(Adherent $manager, CitiesFilters $filters): array
    {
        return $this->votePlaceRepository->findVotePlacesCities($manager, $filters);
    }
}
