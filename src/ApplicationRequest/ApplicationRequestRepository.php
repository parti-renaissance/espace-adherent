<?php

namespace AppBundle\ApplicationRequest;

use AppBundle\ApplicationRequest\Filter\ListFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Repository\ApplicationRequest\RunningMateRequestRepository;
use AppBundle\Repository\ApplicationRequest\VolunteerRequestRepository;

class ApplicationRequestRepository
{
    private $runningMateRepository;
    private $volunteerRepository;

    public function __construct(
        RunningMateRequestRepository $runningMateRepository,
        VolunteerRequestRepository $volunteerRepository
    ) {
        $this->runningMateRepository = $runningMateRepository;
        $this->volunteerRepository = $volunteerRepository;
    }

    public function findOneByUuid(string $uuid, string $type)
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findOneByUuid($uuid);
        }

        return $this->volunteerRepository->findOneByUuid($uuid);
    }

    public function findAllForInseeCodes(array $inseeCodes, string $type, ListFilter $filter = null): array
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findAllForInseeCodes($inseeCodes, $filter);
        }

        return $this->volunteerRepository->findAllForInseeCodes($inseeCodes, $filter);
    }

    public function findAllTakenFor(string $inseeCode, string $type, ListFilter $filter = null): array
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findAllTakenFor($inseeCode, $filter);
        }

        return $this->volunteerRepository->findAllTakenFor($inseeCode, $filter);
    }

    public function findAllForReferentTags(array $referentTags, string $type, ListFilter $filter = null): array
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findForReferentTags($referentTags, $filter);
        }

        return $this->volunteerRepository->findForReferentTags($referentTags, $filter);
    }

    public function updateAdherentRelation(string $email, Adherent $adherent): void
    {
        $this->runningMateRepository->updateAdherentRelation($email, $adherent);
        $this->volunteerRepository->updateAdherentRelation($email, $adherent);
    }

    public function hideDuplicates(ApplicationRequest $request): void
    {
        if ($request instanceof RunningMateRequest) {
            $this->runningMateRepository->hideDuplicates($request);
        } else {
            $this->volunteerRepository->hideDuplicates($request);
        }
    }

    public function countCandidates(array $inseeCodes): int
    {
        return $this->runningMateRepository->countForInseeCodes($inseeCodes) +
            $this->volunteerRepository->countForInseeCodes($inseeCodes);
    }

    public function countTeamMembers(array $inseeCodes): int
    {
        return $this->runningMateRepository->countTakenFor($inseeCodes) +
            $this->volunteerRepository->countTakenFor($inseeCodes);
    }
}
