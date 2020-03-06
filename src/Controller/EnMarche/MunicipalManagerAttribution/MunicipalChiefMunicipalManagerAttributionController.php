<?php

namespace AppBundle\Controller\EnMarche\MunicipalManagerAttribution;

use AppBundle\Entity\Adherent;
use AppBundle\MunicipalManager\Filter\AssociationCityFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-municipales-2020", name="app_municipal_manager_municipal_chief")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefMunicipalManagerAttributionController extends AbstractMunicipalManagerAttributionController
{
    private const SPACE_NAME = 'municipal_chief';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
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
