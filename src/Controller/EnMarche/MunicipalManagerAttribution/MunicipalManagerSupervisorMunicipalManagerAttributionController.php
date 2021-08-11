<?php

namespace App\Controller\EnMarche\MunicipalManagerAttribution;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\MunicipalManager\Filter\AssociationCityFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-responsable-attribution", name="app_municipal_manager_municipal_manager_supervisor")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_MANAGER_SUPERVISOR')")
 */
class MunicipalManagerSupervisorMunicipalManagerAttributionController extends AbstractMunicipalManagerAttributionController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::MUNICIPAL_MANAGER_SUPERVISOR;
    }

    protected function createCityFilter(): AssociationCityFilter
    {
        $filter = new AssociationCityFilter();
        $filter->setManagedTags($this->getReferentTags());

        return $filter;
    }

    private function getReferentTags(): array
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $adherent
            ->getMunicipalManagerSupervisorRole()
            ->getReferent()
            ->getManagedArea()
            ->getTags()
            ->toArray()
        ;
    }
}
