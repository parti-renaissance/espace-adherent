<?php

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Building;
use App\Entity\Pap\CampaignHistory;
use App\Pap\CampaignHistoryStatusEnum;
use App\Repository\Pap\CampaignHistoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')")
 */
class BuildingHistoryController extends AbstractController
{
    /**
     * @Route("/v3/pap/buildings/{uuid}/history",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     name="api_get_building_history",
     *     methods={"GET"}
     * )
     */
    public function __invoke(Building $building, CampaignHistoryRepository $campaignHistoryRepository): JsonResponse
    {
        return $this->json(array_map(function (CampaignHistory $campaignHistory) {
            return [
                    'date' => $campaignHistory->getCreatedAt()->format('Y-m-d'),
                    'building_block' => $campaignHistory->getBuildingBlock(),
                    'floor' => $campaignHistory->getFloor(),
                    'door' => $campaignHistory->getDoor(),
                    'status' => CampaignHistoryStatusEnum::LABELS[$campaignHistory->getStatus()],
                    'questioner' => $campaignHistory->getQuestioner()->getPartialName(),
                ];
        }, $campaignHistoryRepository->findHistoryForBuilding($building, $building->getCurrentCampaign())
        ));
    }
}
