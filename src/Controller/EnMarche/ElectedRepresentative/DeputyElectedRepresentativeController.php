<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
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

    protected function getManagedTags(Request $request): array
    {
        return [$this->getUser()->getManagedDistrict()->getReferentTag()];
    }
}
