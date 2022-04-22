<?php

namespace App\Controller\Api\Pap;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Pap\Campaign;
use App\Repository\Pap\BuildingStatisticsRepository;
use App\Security\Voter\ScopeVisibilityVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetCampaignBuildingStatisticsController extends AbstractController
{
    private BuildingStatisticsRepository $buildingStatisticsRepository;

    public function __construct(BuildingStatisticsRepository $buildingStatisticsRepository)
    {
        $this->buildingStatisticsRepository = $buildingStatisticsRepository;
    }

    public function __invoke(Request $request, Campaign $campaign): PaginatorInterface
    {
        $this->denyAccessUnlessGranted(ScopeVisibilityVoter::PERMISSION, $campaign);

        return $this->buildingStatisticsRepository->findByCampaign(
            $campaign,
            $request->query->getInt('page', 1),
            min($request->query->getInt('page_size', 30), 100)
        );
    }
}
