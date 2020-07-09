<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Entity\Adherent;
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
        return 'deputy';
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return [$adherent->getManagedDistrict()->getReferentTag()];
    }
}
