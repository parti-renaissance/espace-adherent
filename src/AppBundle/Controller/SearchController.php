<?php

namespace AppBundle\Controller;

use AppBundle\Geocoder\Exception\GeocodingException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    /**
     * @Route("/search", name="search_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('search/index.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
        ]);
    }

    /**
     * @Route("/results", name="search_results")
     * @Method("GET")
     */
    public function resultsAction(Request $request)
    {
        $search = $this
            ->get('app.search.search_results_filter')
            ->handleRequest($request)
            ->setMaxResults($this->getParameter('search_max_results'))
        ;

        try {
            $results = $this->get('app.search.search_results_provider')->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/results.html.twig', [
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }
}
