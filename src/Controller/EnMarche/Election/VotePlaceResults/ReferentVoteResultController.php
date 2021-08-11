<?php

namespace App\Controller\EnMarche\Election\VotePlaceResults;

use App\AdherentSpace\AdherentSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/bureaux-de-vote", name="app_vote_results_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentVoteResultController extends DefaultVoteResultController
{
    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::REFERENT;
    }
}
