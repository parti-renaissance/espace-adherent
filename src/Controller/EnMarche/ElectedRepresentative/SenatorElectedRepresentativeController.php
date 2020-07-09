<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-senateur", name="app_senator_elected_representatives_")
 * @Security("is_granted('ROLE_SENATOR')")
 */
class SenatorElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return 'senator';
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return [$adherent->getSenatorArea()->getDepartmentTag()];
    }
}
