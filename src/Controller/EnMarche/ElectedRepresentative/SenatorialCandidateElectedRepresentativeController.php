<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Entity\Adherent;
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
        return 'senatorial_candidate';
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return $adherent->getSenatorialCandidateManagedArea()->getDepartmentTags()->toArray();
    }
}
