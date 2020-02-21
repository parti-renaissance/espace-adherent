<?php

namespace AppBundle\Controller\EnMarche\ElectionVoteResults;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/bureaux-de-vote", name="app_vote_results_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentVoteResultController extends DefaultVoteResultController
{
    private const SPACE_NAME = 'referent';

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }
}
