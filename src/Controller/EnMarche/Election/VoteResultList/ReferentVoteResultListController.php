<?php

namespace App\Controller\EnMarche\Election\VoteResultList;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/assesseurs/communes", name="app_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentVoteResultListController extends AbstractVoteResultListController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}
