<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Entity\Adherent;
use App\Geo\ManagedZoneProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-depute", name="app_deputy_elected_representatives_")
 * @Security("is_granted('ROLE_DEPUTY')")
 */
class DeputyElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return ManagedZoneProvider::DEPUTY;
    }

    protected function getManagedZones(Adherent $adherent): array
    {
        return [$adherent->getManagedDistrict()->getReferentTag()->getZone()];
    }
}
