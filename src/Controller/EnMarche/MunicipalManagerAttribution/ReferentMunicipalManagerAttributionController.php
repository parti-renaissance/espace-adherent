<?php

namespace App\Controller\EnMarche\MunicipalManagerAttribution;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\MunicipalManager\Filter\AssociationCityFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_municipal_manager_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentMunicipalManagerAttributionController extends AbstractMunicipalManagerAttributionController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::REFERENT;
    }

    protected function createCityFilter(): AssociationCityFilter
    {
        $filter = new AssociationCityFilter();
        $filter->setManagedTags($this->getReferentTags());

        return $filter;
    }

    private function getReferentTags(): array
    {
        /** @var Adherent $referent */
        $referent = $this->getUser();

        return $referent->getManagedArea()->getTags()->toArray();
    }
}
