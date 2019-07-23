<?php

namespace AppBundle\ApplicationRequest;

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

    public function findAllForInseeCodes(array $inseeCodes, string $type): array
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findAllForInseeCodes($inseeCodes);
        }

        return $this->volunteerRepository->findAllForInseeCodes($inseeCodes);
    }

    public function findAllTakenFor(array $inseeCodes, string $type): array
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findAllTakenFor($inseeCodes);
        }

        return $this->volunteerRepository->findAllTakenFor($inseeCodes);
    }

    public function findAllForReferentTags(array $referentTags, string $type): array
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findForReferentTags($referentTags);
        }

        return $this->volunteerRepository->findForReferentTags($referentTags);
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
