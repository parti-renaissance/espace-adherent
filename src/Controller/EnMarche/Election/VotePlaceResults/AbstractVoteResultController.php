<?php

namespace AppBundle\Controller\EnMarche\Election\VotePlaceResults;

use AppBundle\Election\ElectionManager;
use AppBundle\Entity\Election\VotePlaceResult;
use AppBundle\Entity\VotePlace;
use AppBundle\Form\Election\VoteResultWithListsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractVoteResultController extends Controller
{
    private $electionManager;
    private $entityManager;

    public function __construct(ElectionManager $electionManager, EntityManagerInterface $entityManager)
    {
        $this->electionManager = $electionManager;
        $this->entityManager = $entityManager;
    }

    protected function submitVoteResultsAction(VotePlace $votePlace, Request $request): Response
    {
        $voteResult = $this->electionManager->getVotePlaceResultForCurrentElectionRound($votePlace, true);

        $form = $this
            ->createForm(VoteResultWithListsType::class, $voteResult, ['data_class' => VotePlaceResult::class])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$voteResult->getId()) {
                $this->entityManager->persist($voteResult);
            }

            $this->entityManager->flush();

            $this->addFlash('info', 'Les résultats ont bien été sauvegardés');

            return $this->getSuccessRedirectionResponse($request);
        }

        return $this->renderTemplate('election_vote_results/vote_place_result_form.html.twig', [
            'form' => $form->createView(),
            'vote_result' => $voteResult,
        ]);
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('election_vote_results/_base_%s_space.html.twig', $spaceType = $this->getSpaceType()),
                'space_type' => $spaceType,
            ]
        ));
    }

    abstract protected function getSpaceType(): string;

    protected function getSuccessRedirectionResponse(Request $request): Response
    {
        return $this->redirectToRoute($request->attributes->get('_route'));
    }
}
