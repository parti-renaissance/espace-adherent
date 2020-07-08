<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Controller\AccessDelegatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent-partage/{delegated_access_uuid}", name="app_referent_elected_representatives_delegated_")
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_ELECTED_REPRESENTATIVES', request)")
 */
class DelegatedReferentElectedRepresentativeController extends ReferentElectedRepresentativeController
{
    use AccessDelegatorTrait;

    protected function getManagedTags(Request $request): array
    {
        return $this->getMainUser($request)->getManagedArea()->getTags()->toArray();
    }
}
