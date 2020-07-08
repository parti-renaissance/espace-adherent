<?php

namespace App\Controller\EnMarche\Committee;

use App\Controller\AccessDelegatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent-partage/{delegated_access_uuid}/comites", name="app_referent_delegated_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT')")
 */
class DelegatedReferentCommitteeController extends ReferentCommitteeController
{
    use AccessDelegatorTrait;
}
