<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Pap\Campaign;
use App\Repository\Pap\BuildingStatisticsRepository;
use App\Security\Voter\ScopeVisibilityVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetCampaignBuildingStatisticsController extends AbstractController
{
    private const ALLOWED_SORT_BY = [
        'building.type',
        'status',
        'nb_visited_doors',
    ];

    private BuildingStatisticsRepository $buildingStatisticsRepository;

    public function __construct(BuildingStatisticsRepository $buildingStatisticsRepository)
    {
        $this->buildingStatisticsRepository = $buildingStatisticsRepository;
    }

    public function __invoke(Request $request, Campaign $campaign): PaginatorInterface
    {
        $this->denyAccessUnlessGranted(ScopeVisibilityVoter::PERMISSION, $campaign);

        if (!\is_array($order = $request->query->all('order'))) {
            $order = [];
        }

        foreach (array_diff(array_keys($order), self::ALLOWED_SORT_BY) as $key) {
            if (\array_key_exists($key, $order)) {
                unset($order[$key]);
            }
        }

        foreach ($order as &$value) {
            if (!\in_array($value, ['asc', 'desc'])) {
                $value = 'asc';
            }
        }

        return $this->buildingStatisticsRepository->findByCampaign(
            $campaign,
            $request->query->getInt('page', 1),
            min($request->query->getInt('page_size', 30), 100),
            $order
        );
    }
}
