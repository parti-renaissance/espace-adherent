<?php

namespace App\Controller\EnMarche\Election\VotePlaceResults;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-communal/assesseurs/bureaux-de-vote", name="app_vote_results_municipal_manager")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_MANAGER')")
 */
class MunicipalManagerVoteResultController extends DefaultVoteResultController
{
    private const SPACE_NAME = 'municipal_manager';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }
}
