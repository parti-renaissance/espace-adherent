<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-des-questionnaires", name="app_jecoute_manager_")
 *
 * @IsGranted("ROLE_JECOUTE_MANAGER")
 */
class JecouteManagerController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::MANAGER_SPACE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getJecouteManagedArea()->getZone()];
    }
}
