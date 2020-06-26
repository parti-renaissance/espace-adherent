<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent-delegue/{delegated_access_uuid}", name="app_referent_elected_representatives_delegated_")
 * @Security("is_granted('ROLE_DELEGATED_REFERENT')")
 */
class DelegatedReferentElectedRepresentativeController extends ReferentElectedRepresentativeController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    protected function getManagedTags(Request $request): array
    {
        return $this->getMainUser($request)->getManagedArea()->getTags()->toArray();
    }
}
