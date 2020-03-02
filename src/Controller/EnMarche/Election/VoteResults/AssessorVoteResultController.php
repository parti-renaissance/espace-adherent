<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResults;

use AppBundle\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-assesseur", name="app_vote_results_assessor")
 *
 * @Security("is_granted('ROLE_ASSESSOR')")
 */
class AssessorVoteResultController extends AbstractVoteResultController
{
    private const SPACE_NAME = 'assessor';

    /**
     * @Route("/resultats", name="_index", methods={"GET", "POST"})
     */
    public function voteResultsAction(Request $request, UserInterface $user): Response
    {
        /** @var Adherent $user */
        return $this->submitVoteResultsAction($user->getAssessorRole()->getVotePlace(), $request);
    }

    protected function getSpaceType(): string
    {
        return self::SPACE_NAME;
    }
}
