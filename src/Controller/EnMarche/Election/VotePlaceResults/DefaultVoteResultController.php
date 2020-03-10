<?php

namespace AppBundle\Controller\EnMarche\Election\VotePlaceResults;

use AppBundle\Entity\VotePlace;
use AppBundle\Security\Voter\ManageVotePlaceVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class DefaultVoteResultController extends AbstractVoteResultController
{
    /**
     * @Route("/{id}/resultats", name="_index", methods={"GET", "POST"})
     */
    public function voteResultsAction(VotePlace $votePlace, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ManageVotePlaceVoter::MANAGE_VOTE_PLACE, $votePlace);

        return $this->submitVoteResultsAction($votePlace, $request);
    }

    protected function getSuccessRedirectionResponse(Request $request): Response
    {
        $params = [];

        if ($request->query->has('f')) {
            $params['f'] = (array) $request->query->get('f');
        }

        if ($request->query->has('page')) {
            $params['page'] = $request->query->getInt('page');
        }

        return $this->redirectToRoute(sprintf('app_assessors_%s_attribution_form', $this->getSpaceType()), $params);
    }
}
