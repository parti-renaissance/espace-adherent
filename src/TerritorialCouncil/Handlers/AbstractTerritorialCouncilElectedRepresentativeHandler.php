<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\UserListDefinitionEnum;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository;
use App\Repository\ElectedRepresentative\MandateRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractTerritorialCouncilElectedRepresentativeHandler extends AbstractTerritorialCouncilHandler
{
    /** @var Mandate[]|array */
    protected $mandates = [];
    /** @var MandateRepository */
    protected $mandateRepository;

    public function __construct(
        EntityManagerInterface $em,
        TerritorialCouncilRepository $repository,
        EventDispatcherInterface $dispatcher,
        PoliticalCommitteeManager $politicalCommitteeManager,
        CommitteeAdherentMandateRepository $committeeMandateRepository,
        TerritorialCouncilAdherentMandateRepository $tcMandateRepository,
        MandateRepository $mandateRepository
    ) {
        parent::__construct(
            $em,
            $repository,
            $dispatcher,
            $politicalCommitteeManager,
            $committeeMandateRepository,
            $tcMandateRepository
        );

        $this->mandateRepository = $mandateRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        $this->mandates = $this->mandateRepository->findByTypesAndUserListDefinitionForAdherent(
            $this->getMandateTypes(),
            UserListDefinitionEnum::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
            $adherent
        );

        return true;
    }

    protected function findTerritorialCouncils(Adherent $adherent): array
    {
        return \count($this->mandates) > 0 ? $this->repository->findByMandates($this->mandates) : [];
    }

    protected function getQualityZone(Adherent $adherent): string
    {
        $zone = $this->mandates[0]->getGeoZone();

        return $zone->isCity() ? $zone->getNameCode() : $zone->getName();
    }

    abstract protected function getMandateTypes(): array;
}
