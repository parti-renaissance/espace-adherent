<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResults;

use AppBundle\Election\ElectionManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\VotePlace;
use AppBundle\Form\VoteResultType;
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
        /** @var Adherent $adherent */
        $voteResult = $this->electionManager->getVoteResultForCurrentElectionRound($votePlace);

        $form = $this
            ->createForm(VoteResultType::class, $voteResult)
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

        return $this->renderTemplate('election_vote_results/index.html.twig', [
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
