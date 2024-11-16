<?php

namespace App\Controller\EnMarche;

use App\AssociationCity\Filter\AssociationCityFilter;
use App\Form\CityFilterType;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ELECTION_RESULTS_REPORTER')]
#[Route(path: '/espace-rapporteur-resultats', name: 'app_election_results_reporter_space')]
class ElectionResultsReporterController extends AbstractController
{
    #[Route(path: '/communes', name: '_cities_list', methods: ['GET'])]
    public function listCitiesAction(Request $request, CityRepository $cityRepository): Response
    {
        $form = $this
            ->createForm(CityFilterType::class, $filter = new AssociationCityFilter())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new AssociationCityFilter();
        }

        return $this->render('election_results_reporter/cities_list.html.twig', [
            'form' => $form->createView(),
            'cities' => $cityRepository->findAllForFilter($filter, $request->query->getInt('page', 1)),
        ]);
    }
}
