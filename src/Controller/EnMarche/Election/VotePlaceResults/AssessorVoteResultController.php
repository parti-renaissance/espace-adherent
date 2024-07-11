<?php

namespace App\Controller\EnMarche\Election\VotePlaceResults;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ASSESSOR')]
#[Route(path: '/espace-assesseur', name: 'app_vote_results_assessor')]
class AssessorVoteResultController extends AbstractVoteResultController
{
    #[Route(path: '/resultats', name: '_index', methods: ['GET', 'POST'])]
    public function voteResultsAction(Request $request): Response
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        return $this->submitVoteResultsAction($user->getAssessorRole()->getVotePlace(), $request);
    }

    protected function getSpaceType(): string
    {
        return AdherentSpaceEnum::ASSESSOR;
    }
}
