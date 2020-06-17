<?php

namespace App\Controller\EnMarche;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-depute-delegue/{delegated_access_uuid}", name="app_deputy_delegated_")
 * @Security("is_granted('ROLE_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_COMMITTEE', request)")
 */
class DelegatedDeputyController extends DeputyController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;
}
