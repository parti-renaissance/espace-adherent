<?php

declare(strict_types=1);

namespace App\Controller\Api\Phoning;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Entity\Phoning\CampaignHistory;
use App\Repository\AdherentRepository;
use App\Security\Voter\PhoningCampaignVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/phoning_campaigns/{uuid}/start', name: 'api_phoning_campaign_start_campaign_for_one_adherent', methods: ['POST'], requirements: ['uuid' => '%pattern_uuid%'])]
class StartCampaignController extends AbstractController
{
    public function __invoke(
        Campaign $campaign,
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        /** @var Adherent $connectedAdherent */
        $connectedAdherent = $this->getUser();
        if (!$campaign->isPermanent()) {
            $this->denyAccessUnlessGranted(PhoningCampaignVoter::PERMISSION, $campaign);
        }

        if ($campaign->isFinished()) {
            return $this->json([
                'code' => 'finished_campaign',
                'message' => 'Cette campagne est terminée',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$campaign->isPermanent() && !$adherent = $adherentRepository->findOneToCall($campaign, $connectedAdherent)) {
            return $this->json([
                'code' => 'no_available_number',
                'message' => 'Aucun numéro à appeler disponible',
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist(
            $campaignHistory = CampaignHistory::createForCampaign($campaign, $connectedAdherent, $adherent ?? null)
        );
        $entityManager->flush();

        return $this->json($campaignHistory, Response::HTTP_CREATED, [], ['groups' => ['phoning_campaign_call_read']]);
    }
}
