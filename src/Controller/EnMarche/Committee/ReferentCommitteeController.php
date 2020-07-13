<?php

namespace App\Controller\EnMarche\Committee;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/comites", name="app_referent_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_COMMITTEE'))")
 */
class ReferentCommitteeController extends AbstractCommitteeController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}
