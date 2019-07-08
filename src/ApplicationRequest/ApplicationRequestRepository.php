<?php

namespace AppBundle\ApplicationRequest;

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

    public function findAllForReferentTags(array $referentTags, string $type): array
    {
        if (ApplicationRequestTypeEnum::RUNNING_MATE === $type) {
            return $this->runningMateRepository->findForReferentTags($referentTags);
        }

        return $this->volunteerRepository->findForReferentTags($referentTags);
    }
}
