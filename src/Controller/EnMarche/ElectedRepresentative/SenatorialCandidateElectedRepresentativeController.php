<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-senatoriales", name="app_senatorial_candidate_elected_representatives_")
 * @Security("is_granted('ROLE_SENATORIAL_CANDIDATE')")
 */
class SenatorialCandidateElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return 'senatorial_candidate';
    }

    protected function getManagedTags(): array
    {
        return $this->getUser()->getSenatorialCandidateManagedArea()->getDepartmentTags()->toArray();
    }
}
