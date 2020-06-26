<?php

namespace App\Controller\EnMarche\Committee;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent-delegue/{delegated_access_uuid}/comites", name="app_referent_delegated_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT')")
 */
class DelegatedReferentCommitteeController extends ReferentCommitteeController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;
}
