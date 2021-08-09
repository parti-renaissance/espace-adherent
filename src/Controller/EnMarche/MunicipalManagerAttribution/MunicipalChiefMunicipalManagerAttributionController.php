<?php

namespace App\Controller\EnMarche\MunicipalManagerAttribution;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\MunicipalManager\Filter\AssociationCityFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-municipales-2020", name="app_municipal_manager_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefMunicipalManagerAttributionController extends AbstractMunicipalManagerAttributionController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::MUNICIPAL_CHIEF;
    }

    protected function createCityFilter(): AssociationCityFilter
    {
        $filter = new AssociationCityFilter();
        $filter->setManagedInseeCode($this->getManagedInseeCode());

        return $filter;
    }

    private function getManagedInseeCode(): ?string
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $adherent
            ->getMunicipalChiefManagedArea()
            ->getInseeCode()
        ;
    }
}
