<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_JECOUTE_MANAGER')]
#[Route(path: '/espace-responsable-des-questionnaires', name: 'app_jecoute_manager_')]
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
