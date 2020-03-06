<?php

namespace AppBundle\Controller\EnMarche\MunicipalManagerAttribution;

use AppBundle\Entity\Adherent;
use AppBundle\MunicipalManager\Filter\AssociationCityFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent", name="app_municipal_manager_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentMunicipalManagerAttributionController extends AbstractMunicipalManagerAttributionController
{
    private const SPACE_NAME = 'referent';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
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
