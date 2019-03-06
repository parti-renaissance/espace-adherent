<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\CitizenActionCategory;
use AppBundle\Entity\Committee;
use AppBundle\Entity\EntityPostAddressTrait;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventCategory;
use AppBundle\Entity\EventGroupCategory;
use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Search\SearchParametersFilter;
use AppBundle\Search\SearchResultsProvidersManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    /**
     * @Route("/evenements", name="app_search_events")
     * @Route("/evenements/categorie/{slug}", name="app_search_events_by_category")
     * @Method("GET")
     */
    public function searchEventsAction(Request $request, string $slug = null)
    {
        if ($slug) {
            if ($category = $this->getDoctrine()->getRepository(EventCategory::class)->findOneBySlug($slug)) {
                $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_EVENTS);
                $request->query->set(SearchParametersFilter::PARAMETER_EVENT_CATEGORY, $category->getId());
            } elseif ($this->getDoctrine()->getRepository(CitizenActionCategory::class)->findOneBySlug($slug)) {
                $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_CITIZEN_ACTIONS);
                $request->query->set(SearchParametersFilter::PARAMETER_EVENT_CATEGORY, SearchParametersFilter::TYPE_CITIZEN_ACTIONS);
            } else {
                return $this->redirectToRoute('app_search_events');
            }
        } else {
            $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_EVENTS);
        }

        $search = $this->get(SearchParametersFilter::class)->handleRequest($request);
        $user = $this->getUser();
        if ($user && \in_array(EntityPostAddressTrait::class, class_uses($user))) {
            $search->setCity(sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
        }

        try {
            $results = $this->get(SearchResultsProvidersManager::class)->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/search_events.html.twig', [
            'event_categories' => $this->getDoctrine()->getRepository(EventGroupCategory::class)->findAllEnabledOrderedByName(),
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

        $search = $this->get(SearchParametersFilter::class)->handleRequest($request);
        $user = $this->getUser();
        if ($user && \in_array(EntityPostAddressTrait::class, class_uses($user))) {
            $search->setCity(sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
        }

        try {
            $results = $this->get(SearchResultsProvidersManager::class)->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/search_committees.html.twig', [
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    /**
     * @Route("/recherche/projets-citoyens", name="app_search_citizen_projects")
     * @Method("GET")
     */
    public function searchCitizenProjectsAction()
    {
        return $this->redirectToRoute('react_app_citizen_projects_search', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/recherche", name="app_search")
     * @Method("GET")
     */
    public function resultsAction(Request $request)
    {
        $search = $this->get(SearchParametersFilter::class)->handleRequest($request);

        try {
            $results = $this->get(SearchResultsProvidersManager::class)->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->get('translator')->trans('search.geocoding.exception');
        }

        return $this->render('search/results.html.twig', [
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    /**
     * @Route("/tous-les-evenements/{page}", requirements={"page": "\d+"}, name="app_search_all_events")
     * @Method("GET")
     */
    public function allEventsAction(int $page = 1)
    {
        $eventRepository = $this->getDoctrine()->getRepository(Event::class);
        $maxResultPage = $this->getParameter('search_max_results');
        $results = $eventRepository->paginate($page > 1 ? $maxResultPage * $page : 0);
        $totalResults = $results->count();
        $totalPage = (int) ceil($totalResults / $maxResultPage);

        if (!$results->count() || !($page <= $totalPage)) {
            throw $this->createNotFoundException('No results found');
        }

        return $this->render('events/all_events.html.twig', [
            'results' => $results,
            'total' => $totalResults,
            'currentPage' => $page,
            'totalPages' => $totalPage,
        ]);
    }

    /**
     * @Route("/tous-les-comites/{page}", requirements={"page": "\d+"}, name="app_search_all_committees")
     * @Method("GET")
     */
    public function allCommitteesAction(int $page = 1)
    {
        $committeeRepository = $this->getDoctrine()->getRepository(Committee::class);
        $maxResultPage = $this->getParameter('search_max_results');
        $results = $committeeRepository->paginate($page > 1 ? $maxResultPage * $page : 0);
        $totalResults = $results->count();
        $totalPage = (int) ceil($totalResults / $maxResultPage);

        if (!$results->count() || !($page <= $totalPage)) {
            throw $this->createNotFoundException('No results found');
        }

        return $this->render('committee/all_committees.html.twig', [
            'results' => $results,
            'total' => $totalResults,
            'currentPage' => $page,
            'totalPages' => $totalPage,
        ]);
    }
}
