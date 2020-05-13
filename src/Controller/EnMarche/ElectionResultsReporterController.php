<?php

namespace App\Controller\EnMarche;

use App\Form\CityFilterType;
use App\MunicipalManager\Filter\AssociationCityFilter;
use App\Repository\CityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-rapporteur-resultats", name="app_election_results_reporter_space")
 *
 * @Security("is_granted('ROLE_ELECTION_RESULTS_REPORTER')")
 */
class ElectionResultsReporterController extends Controller
{
    /**
     * @Route("/communes", name="_cities_list", methods={"GET"})
     */
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
