<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\ReferentTagRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use Doctrine\ORM\EntityManagerInterface;

class TerritorialCouncilCommitteeSupervisorHandler extends AbstractTerritorialCouncilHandler
{
    /** @var ReferentTagRepository */
    private $referentTagRepository;

    public function __construct(
        EntityManagerInterface $em,
        TerritorialCouncilRepository $repository,
        ReferentTagRepository $referentTagRepository
    ) {
        parent::__construct($em, $repository);

        $this->referentTagRepository = $referentTagRepository;
    }

    protected function findTerritorialCouncils(Adherent $adherent): array
    {
        return $this->repository->findForSupervisor($adherent);
    }

    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR;
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        return $adherent->getMemberships()->getCommitteeSupervisorMemberships()->first()->getCommittee()->getName();
    }
}
