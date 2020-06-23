<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent", name="app_referent_elected_representatives_")
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }

    protected function getManagedTags(): array
    {
        return $this->getUser()->getManagedArea()->getTags()->toArray();
    }
}
