<?php

namespace App\Controller\Api\Statistics;

use App\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_OAUTH_SCOPE_READ:STATS')]
#[Route(path: '/statistics/adherents')]
class AdherentsController extends AbstractStatisticsController
{
    #[Route(path: '/count', name: 'app_statistics_adherents_count', methods: ['GET'])]
    public function adherentsCountAction(AdherentRepository $adherentRepository): Response
    {
        $count = $adherentRepository->countByGender();

        return new JsonResponse($this->aggregateCount($count));
    }

    private function aggregateCount(array $count): array
    {
        array_walk($count, function (&$item) {
            $item = (int) $item['count'];
        });

        $count['total'] = array_sum($count);

        return $count;
    }
}
