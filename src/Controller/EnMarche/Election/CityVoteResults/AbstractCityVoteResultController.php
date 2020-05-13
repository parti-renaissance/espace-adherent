<?php

namespace App\Controller\EnMarche\Election\CityVoteResults;

use App\Election\ElectionManager;
use App\Entity\City;
use App\Entity\Election\CityVoteResult;
use App\Form\Election\VoteResultWithListsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractCityVoteResultController extends Controller
{
    private $electionManager;
    private $entityManager;

    public function __construct(ElectionManager $electionManager, EntityManagerInterface $entityManager)
    {
        $this->electionManager = $electionManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/communes/{id}/resultats", name="_city_vote_results_edit", methods={"GET", "POST"})
     */
    public function __invoke(City $city, Request $request): Response
    {
        $cityVoteResult = $this->electionManager->getCityVoteResultForCurrentElectionRound($city, true);

        $form = $this
            ->createForm(VoteResultWithListsType::class, $cityVoteResult, ['data_class' => CityVoteResult::class])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$cityVoteResult->getId()) {
                $this->entityManager->persist($cityVoteResult);
            }

            $this->entityManager->flush();

            $this->addFlash('info', 'Les résultats ont bien été sauvegardés');

            return $this->redirectToRoute(sprintf('app_%s_city_vote_results_edit', $this->getSpaceType()), ['id' => $city->getId()]);
        }

        return $this->renderTemplate('election_vote_results/city_vote_results_form.html.twig', [
            'form' => $form->createView(),
            'vote_result' => $cityVoteResult,
        ]);
    }

    abstract protected function getSpaceType(): string;

    private function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('election_vote_results/_base_%s_space.html.twig', $spaceType = $this->getSpaceType()),
                'space_type' => $spaceType,
            ]
        ));
    }
}
