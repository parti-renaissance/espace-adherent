<?php

namespace App\Controller\EnMarche\Election\VoteResultList;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_REFERENT')]
#[Route(path: '/espace-referent/assesseurs/communes', name: 'app_referent')]
class ReferentVoteResultListController extends AbstractVoteResultListController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}
