<?php

namespace App\Controller\EnMarche\Jecoute\Personalization;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/campagne", name="app_jecoute_referent_region_")
 *
 * @Security("is_granted('ROLE_JECOUTE_REGION') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE_REGION'))")
 */
class JecouteReferentRegionController extends AbstractPersonalizationController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::REFERENT_SPACE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray());
    }
}
