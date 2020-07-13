<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-la-republique-ensemble", name="app_lre_elected_representatives_")
 * @Security("is_granted('ROLE_LRE')")
 */
class LreElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return 'lre';
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return [$adherent->getLreArea()->getReferentTag()];
    }
}
