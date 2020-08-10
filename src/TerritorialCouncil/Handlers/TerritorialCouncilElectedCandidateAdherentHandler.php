<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\CommitteeRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use Doctrine\ORM\EntityManagerInterface;

class TerritorialCouncilElectedCandidateAdherentHandler extends AbstractTerritorialCouncilHandler
{
    /** @var Committee[]|array */
    protected $committees = [];
    /** @var CommitteeRepository */
    protected $committeeRepository;

    public function __construct(
        EntityManagerInterface $em,
        TerritorialCouncilRepository $repository,
        CommitteeRepository $committeeRepository
    ) {
        parent::__construct($em, $repository);

        $this->committeeRepository = $committeeRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        $this->committees = $this->committeeRepository->findForElectedAdherent($adherent);

        return true;
    }

    protected function findTerritorialCouncils(Adherent $adherent): array
    {
        return \count($this->committees) > 0 ? $this->repository->findByCommittees($this->committees) : [];
    }

    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT;
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        return $this->committees[0]->getName();
    }
}
