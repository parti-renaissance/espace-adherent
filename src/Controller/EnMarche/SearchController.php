<?php

namespace App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Entity\EntityPostAddressTrait;
use App\Geocoder\Exception\GeocodingException;
use App\Repository\CommitteeRepository;
use App\Repository\Event\EventRepository;
use App\Repository\EventCategoryRepository;
use App\Repository\EventGroupCategoryRepository;
use App\Search\SearchParametersFilter;
use App\Search\SearchResultsProvidersManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchController extends AbstractController
{
    private $searchParametersFilter;
    private $searchResultsProvidersManager;
    private $translator;

    public function __construct(
        SearchParametersFilter $searchParametersFilter,
        SearchResultsProvidersManager $searchResultsProvidersManager,
        TranslatorInterface $translator,
    ) {
        $this->searchParametersFilter = $searchParametersFilter;
        $this->searchResultsProvidersManager = $searchResultsProvidersManager;
        $this->translator = $translator;
    }

    #[Route(path: '/evenements', name: 'app_search_events', methods: ['GET'])]
    #[Route(path: '/evenements/categorie/{slug}', name: 'app_search_events_by_category')]
    public function searchEventsAction(
        Request $request,
        EventGroupCategoryRepository $eventGroupCategoryRepository,
        EventCategoryRepository $eventCategoryRepository,
        ?string $slug = null,
    ): Response {
        if ($slug) {
            if ($category = $eventCategoryRepository->findOneBySlug($slug)) {
                $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_EVENTS);
                $request->query->set(SearchParametersFilter::PARAMETER_EVENT_CATEGORY, $category->getId());
            } else {
                return $this->redirectToRoute('app_search_events');
            }
        } else {
            $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_EVENTS);
        }

        $search = $this->searchParametersFilter->handleRequest($request);
        $user = $this->getUser();
        if ($user && \in_array(EntityPostAddressTrait::class, class_uses($user))) {
            $search->setCity(\sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
        }

        if ($user instanceof Adherent) {
            $search->setWithPrivate(true);
        }

        try {
            $results = $this->searchResultsProvidersManager->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->translator->trans('search.geocoding.exception');
        }

        return $this->render('search/search_events.html.twig', [
            'event_categories' => $eventGroupCategoryRepository->findAllEnabledOrderedByName(),
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    #[Route(path: '/comites', name: 'app_search_committees', methods: ['GET'])]
    public function searchCommitteesAction(Request $request): Response
    {
        $request->query->set(SearchParametersFilter::PARAMETER_TYPE, SearchParametersFilter::TYPE_COMMITTEES);

        $search = $this->searchParametersFilter->handleRequest($request);
        $user = $this->getUser();
        if ($user && \in_array(EntityPostAddressTrait::class, class_uses($user))) {
            $search->setCity(\sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
        }

        try {
            $results = $this->searchResultsProvidersManager->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->translator->trans('search.geocoding.exception');
        }

        return $this->render('search/search_committees.html.twig', [
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    #[Route(path: '/recherche', name: 'app_search', methods: ['GET'])]
    public function resultsAction(Request $request): Response
    {
        $search = $this->searchParametersFilter->handleRequest($request);

        if ($this->getUser() instanceof Adherent) {
            $search->setWithPrivate(true);
        }

        try {
            $results = $this->searchResultsProvidersManager->find($search);
        } catch (GeocodingException $exception) {
            $errors[] = $this->translator->trans('search.geocoding.exception');
        }

        return $this->render('search/results.html.twig', [
            'search' => $search,
            'results' => $results ?? [],
            'errors' => $errors ?? [],
        ]);
    }

    #[Route(path: '/tous-les-evenements/{page}', requirements: ['page' => '\d+'], name: 'app_search_all_events', methods: ['GET'])]
    public function allEventsAction(EventRepository $eventRepository, int $page = 1): Response
    {
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

    #[Route(path: '/tous-les-comites/{page}', requirements: ['page' => '\d+'], name: 'app_search_all_committees', methods: ['GET'])]
    public function allCommitteesAction(CommitteeRepository $committeeRepository, int $page = 1): Response
    {
        $maxResultPage = $this->getParameter('search_max_results');
        $results = $committeeRepository->paginateAllApprovedCommittees($page > 1 ? $maxResultPage * $page : 0);
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
