<?php

namespace AppBundle\Controller;

use AppBundle\Event\EventCategories;
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
    public function searchEventsAction(Request $request)
    {
        $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_EVENTS);

        $search = $this->getSearch($request);

        try {
            $results = $this->get('app.search.search_results_provider')->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/search_events.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
            'search_type' => SearchParametersFilter::TYPE_EVENTS,
            'event_categories' => EventCategories::CHOICES,
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    /**
     * @Route("/comites", name="app_search_committees")
     * @Method("GET")
     */
    public function searchCommitteesAction(Request $request)
    {
        $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_COMMITTEES);

        $search = $this->getSearch($request);

        try {
            $results = $this->get('app.search.search_results_provider')->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/search.html.twig', [
            'search_max_results' => $this->getParameter('search_max_results'),
            'search_type' => SearchParametersFilter::TYPE_COMMITTEES,
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    /**
     * @Route("/recherche", name="app_search")
     * @Method("GET")
     */
    public function resultsAction(Request $request)
    {
        $search = $this->getSearch($request);

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

    private function getSearch(Request $request): SearchParametersFilter
    {
        return $this
            ->get('app.search.search_results_filter')
            ->setMaxResults($this->getParameter('search_max_results'))
            ->handleRequest($request);
    }
}
