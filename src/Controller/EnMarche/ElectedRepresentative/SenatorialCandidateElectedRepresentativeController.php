<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\ReferentTag;
use App\Geo\ManagedZoneProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-senatoriales", name="app_senatorial_candidate_elected_representatives_")
 * @Security("is_granted('ROLE_SENATORIAL_CANDIDATE') or (is_granted('ROLE_DELEGATED_SENATORIAL_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_ELECTED_REPRESENTATIVES'))")
 */
class SenatorialCandidateElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return ManagedZoneProvider::SENATORIAL_CANDIDATE;
    }

    protected function getManagedZones(Adherent $adherent): array
    {
        $zones = [];

        /* @var ReferentTag $referentTag */
        $referentTags = $adherent->getSenatorialCandidateManagedArea()->getDepartmentTags();
        foreach ($referentTags as $referentTag) {
            $zones[] = $referentTag->getZone();
        }

        return $zones;
    }
}
