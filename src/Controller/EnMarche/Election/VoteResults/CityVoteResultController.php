<?php

namespace AppBundle\Controller\EnMarche\Election\VoteResults;

use AppBundle\Election\ElectionManager;
use AppBundle\Entity\City;
use AppBundle\Form\CityVoteResultType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-rapporteur-resultats/communes/{id}/resultats", name="app_city_vote_results_edit", methods={"GET", "POST"})
 *
 * @Security("is_granted('ROLE_ELECTION_RESULTS_REPORTER')")
 */
class CityVoteResultController extends Controller
{
    private $electionManager;
    private $entityManager;

    public function __construct(ElectionManager $electionManager, EntityManagerInterface $entityManager)
    {
        $this->electionManager = $electionManager;
        $this->entityManager = $entityManager;
    }

    public function __invoke(City $city, Request $request): Response
    {
        $cityVoteResult = $this->electionManager->getCityVoteResultForCurrentElectionRound($city);

        $form = $this
            ->createForm(CityVoteResultType::class, $cityVoteResult)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$cityVoteResult->getId()) {
                $this->entityManager->persist($cityVoteResult);
            }

            $this->entityManager->flush();

            $this->addFlash('info', 'Les résultats ont bien été sauvegardés');

            return $this->redirectToRoute('app_city_vote_results_edit', ['id' => $city->getId()]);
        }

        return $this->render('election_results_reporter/form.html.twig', [
            'form' => $form->createView(),
            'vote_result' => $cityVoteResult,
        ]);
    }
}
