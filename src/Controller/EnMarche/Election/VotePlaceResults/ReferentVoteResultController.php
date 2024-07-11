<?php

namespace App\Controller\EnMarche\Election\VotePlaceResults;

use App\AdherentSpace\AdherentSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_REFERENT')]
#[Route(path: '/espace-referent/bureaux-de-vote', name: 'app_vote_results_referent')]
class ReferentVoteResultController extends DefaultVoteResultController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::REFERENT;
    }
}
