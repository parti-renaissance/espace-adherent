<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\CampaignHistory;
use App\Phoning\CampaignHistoryStatusEnum;
use App\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/v3/phoning_campaigns/survey/{uuid}", requirements={"uuid": "%pattern_uuid%"}, name="api_get_phoning_campaign_survey", methods={"GET"})
 *
 * @Security("is_granted('ROLE_PHONING_CAMPAIGN_MEMBER') and is_granted('IS_CAMPAIGN_HISTORY_CALLER', campaignHistory)")
 */
class GetCampaignSurveyController extends AbstractController
{
    public function __invoke(
        CampaignHistory $campaignHistory,
        UserInterface $connectedAdherent,
        AdherentRepository $adherentRepository
    ): JsonResponse {
        $campaign = $campaignHistory->getCampaign();
        if ($campaign->isFinished()) {
            return $this->json(['message' => 'Cette campagne est terminée'], Response::HTTP_BAD_REQUEST,
            [], ['json_encode_options' => \JSON_FORCE_OBJECT]);
        }

        return $this->json([
            'call_status' => [
                'finished' => CampaignHistoryStatusEnum::LABEL_FINISHED_STATUS,
                'interrupted' => CampaignHistoryStatusEnum::LABEL_INTERRUPTED_STATUS,
            ],
            'questions' => array_merge(!$campaignHistory->getAdherent()->isEmailUnsubscribed() ? [
                    'become_caller' => [
                        'label' => ' Souhaiteriez-vous devenir appelant ?',
                        'responses' => [
                            true => 'Oui',
                            false => 'Non',
                        ],
                    ],
                ] : [], [
                'postal_code_checked' => [
                    'label' => 'Code postal à jour ?',
                    'responses' => [
                        true => 'Oui',
                        false => 'Non',
                    ],
                ],
                'need_renewal' => [
                    'label' => 'Souhaiterez-vous vous réabonner ?',
                    'responses' => [
                        true => 'Oui',
                        false => 'Non',
                    ],
                ],
                'call_more' => [
                    'label' => 'Souhaitez-vous être rappelé plus souvent ?',
                    'responses' => [
                        true => 'Oui',
                        false => 'Non',
                    ],
                ],
            ]),
        ], Response::HTTP_OK);
    }
}
