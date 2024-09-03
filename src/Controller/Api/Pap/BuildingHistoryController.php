<?php

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Building;
use App\Repository\Pap\CampaignHistoryRepository;
use App\Repository\Pap\CampaignRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')")]
class BuildingHistoryController extends AbstractController
{
    #[Route(path: '/v3/pap/buildings/{uuid}/history', requirements: ['uuid' => '%pattern_uuid%'], name: 'api_get_building_history', methods: ['GET'])]
    public function __invoke(
        Request $request,
        Building $building,
        CampaignHistoryRepository $campaignHistoryRepository,
        CampaignRepository $campaignRepository,
    ): JsonResponse {
        if (!$campaignUuid = $request->query->get('campaign_uuid')) {
            return $this->json('Parameter "campaign_uuid" is required.', Response::HTTP_BAD_REQUEST);
        }

        if (!$campaign = $campaignRepository->findOneByUuid($campaignUuid)) {
            return $this->json(
                \sprintf('Campaign with uuid "%s" not found.', $campaignUuid),
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            $campaignHistoryRepository->findHistoryForBuilding($building, $campaign),
            JsonResponse::HTTP_OK,
            [],
            ['groups' => ['pap_building_history']]
        );
    }
}
