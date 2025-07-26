<?php

namespace App\Controller\Renaissance\Election;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use Sonata\Exporter\Exporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[IsGranted('ROLE_USER')]
#[Route(path: '/election-sas/{uuid}', name: 'app_sas_election', requirements: ['uuid' => '%pattern_uuid%'])]
class SasElectionController extends AbstractController
{
    #[Route(path: '', name: '_index')]
    public function indexAction(Request $request, AnonymousFollowerSession $anonymousFollowerSession, Designation $designation, ElectionRepository $electionRepository): Response
    {
        if ($response = $anonymousFollowerSession->start($request)) {
            return $response;
        }

        return $this->render('renaissance/election/sas.html.twig', [
            'designation' => $designation,
            'election' => $electionRepository->findOneByDesignation($designation),
        ]);
    }

    #[Route(path: '/reglement', name: '_regulation')]
    public function regulationAction(Designation $designation): Response
    {
        if (!$designation->wordingRegulationPage) {
            throw $this->createNotFoundException();
        }

        return $this->render('renaissance/election/regulation.html.twig', ['designation' => $designation]);
    }

    #[IsGranted('ROLE_VOTE_INSPECTOR')]
    #[Route(path: '/bulletins.csv', name: '_export')]
    public function exportVotesAction(Designation $designation, VoteResultRepository $repository, Exporter $exporter): Response
    {
        if (!$designation->isResultPeriodActive()) {
            throw $this->createAccessDeniedException();
        }

        return $exporter->getResponse(
            'csv',
            (new AsciiSlugger())->slug($designation->getTitle()).'.csv',
            new \ArrayIterator($repository->getVotes($designation))
        );
    }
}
