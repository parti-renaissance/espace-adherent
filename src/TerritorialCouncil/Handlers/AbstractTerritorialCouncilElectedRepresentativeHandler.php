<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\UserListDefinitionEnum;
use App\Repository\ElectedRepresentative\MandateRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractTerritorialCouncilElectedRepresentativeHandler extends AbstractTerritorialCouncilHandler
{
    /** @var Mandate[]|Collection */
    protected $mandates;
    /** @var MandateRepository */
    protected $mandateRepository;

    public function __construct(
        EntityManagerInterface $em,
        TerritorialCouncilRepository $repository,
        MandateRepository $mandateRepository
    ) {
        parent::__construct($em, $repository);

        $this->mandateRepository = $mandateRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        $this->mandates = $this->mandateRepository->findByTypeAndUserListDefinitionForAdherent(
            $this->getMandateType(),
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
        return $this->mandates[0]->getZone()->getName();
    }

    abstract protected function getMandateType(): string;
}
