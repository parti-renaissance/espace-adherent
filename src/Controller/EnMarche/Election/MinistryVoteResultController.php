<?php

namespace App\Controller\EnMarche\Election;

use App\Election\ElectionManager;
use App\Entity\City;
use App\Form\Election\MinistryVoteResultType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_ELECTION_RESULTS_REPORTER')")
 */
class MinistryVoteResultController extends Controller
{
    private $electionManager;
    private $entityManager;

    public function __construct(ElectionManager $electionManager, EntityManagerInterface $entityManager)
    {
        $this->electionManager = $electionManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/espace-rapporteur-resultats/communes/{id}/resultats", name="app_ministry_vote_results_edit", methods={"GET", "POST"})
     */
    public function __invoke(City $city, Request $request): Response
    {
        $ministryVoteResult = $this->electionManager->getMinistryVoteResultForCurrentElectionRound($city);

        $form = $this
            ->createForm(MinistryVoteResultType::class, $ministryVoteResult)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$ministryVoteResult->getId()) {
                $this->entityManager->persist($ministryVoteResult);
            }

            $this->entityManager->flush();

            $this->addFlash('info', 'Les résultats ont bien été sauvegardés');

            return $this->redirectToRoute('app_ministry_vote_results_edit', ['id' => $city->getId()]);
        }

        return $this->render('election_results_reporter/form.html.twig', [
            'form' => $form->createView(),
            'vote_result' => $ministryVoteResult,
        ]);
    }
}
