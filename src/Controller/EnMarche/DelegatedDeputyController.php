<?php

namespace App\Controller\EnMarche;

use App\Controller\AccessDelegatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-depute-delegue", name="app_deputy_delegated_")
 * @Security("is_granted('IS_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_COMMITTEE')")
 */
class DelegatedDeputyController extends DeputyController
{
    use AccessDelegatorTrait;
}
