<?php

namespace App\Controller\EnMarche\Election\VoteResultList;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/assesseurs/communes", name="app_referent")
 *
 * @IsGranted("ROLE_REFERENT")
 */
class ReferentVoteResultListController extends AbstractVoteResultListController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}
