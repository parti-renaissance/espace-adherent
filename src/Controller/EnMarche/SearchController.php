<?php

declare(strict_types=1);

namespace App\Controller\EnMarche;

use App\Repository\CommitteeRepository;
use App\Repository\Event\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route(path: '/evenements', name: 'app_search_events', methods: ['GET'])]
    #[Route(path: '/evenements/categorie/{slug}', name: 'app_search_events_by_category', methods: ['GET'])]
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

    #[Route(path: '/comites', name: 'app_search_committees', methods: ['GET'])]
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
