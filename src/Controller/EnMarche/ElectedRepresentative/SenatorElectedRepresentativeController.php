<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_SENATOR')]
#[Route(path: '/espace-senateur', name: 'app_senator_elected_representatives_')]
class SenatorElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::SENATOR;
    }

    protected function getManagedZones(Adherent $adherent): array
    {
        return [$adherent->getSenatorArea()->getDepartmentTag()->getZone()];
    }
}
