<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_DEPUTY')]
#[Route(path: '/espace-depute', name: 'app_deputy_elected_representatives_')]
class DeputyElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::DEPUTY;
    }

    protected function getManagedZones(Adherent $adherent): array
    {
        return [$adherent->getDeputyZone()];
    }
}
