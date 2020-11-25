<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Entity\Adherent;
use App\Geo\ManagedZoneProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent", name="app_referent_elected_representatives_")
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_ELECTED_REPRESENTATIVES'))")
 */
class ReferentElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return ManagedZoneProvider::REFERENT;
    }

    protected function getManagedZones(Adherent $adherent): array
    {
        return $adherent->getManagedArea()->getZones()->toArray();
    }
}
