<?php

namespace AppBundle\Controller;

use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Search\SearchParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    /**
     * @Route("/evenements", name="app_search_events")
     * @Method("GET")
     */
    public function searchEventsAction()
    {
        return $this->render('search/search_events.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
            'search_type' => SearchParametersFilter::TYPE_EVENTS,
        ]);
    }

    /**
     * @Route("/comites", name="app_search_committees")
     * @Method("GET")
     */
    public function searchCommitteesAction()
    {
        return $this->render('search/search.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
            'search_type' => SearchParametersFilter::TYPE_COMMITTEES,
        ]);
    }

    /**
     * @Route("/recherche", name="app_search")
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
