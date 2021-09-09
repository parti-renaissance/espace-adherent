<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\CampaignHistory;
use App\Phoning\CampaignHistoryStatusEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/phoning_campaign_histories/{uuid}/survey-config", requirements={"uuid": "%pattern_uuid%"}, name="api_get_phoning_campaign_history_survey_config", methods={"GET"})
 *
 * @Security("is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')")
 */
class GetPhoningCampaignHistoriesSurveyConfigController extends AbstractController
{
    public function __invoke(CampaignHistory $campaignHistory): JsonResponse
    {
        return $this->json([
            'call_status' => [
                'finished' => self::transformStatusArray(CampaignHistoryStatusEnum::FINISHED_STATUS),
                'interrupted' => self::transformStatusArray(CampaignHistoryStatusEnum::INTERRUPTED_STATUS),
            ],
            'satisfaction_questions' => array_merge($campaignHistory->getAdherent()->isEmailUnsubscribed() ? [[
                'code' => 'need_renewal',
                'label' => 'Souhaiterez-vous vous réabonner ?',
                'type' => 'boolean',
            ]] : [],
            [
                [
                    'code' => 'postal_code_checked',
                    'label' => 'Code postal à jour ?',
                    'type' => 'boolean',
                ],
                [
                    'code' => 'become_caller',
                    'label' => 'Souhaiteriez-vous devenir appelant ?',
                    'type' => 'boolean',
                ],
                [
                    'code' => 'call_more',
                    'label' => 'Souhaitez-vous être rappelé plus souvent ?',
                    'type' => 'boolean',
                ],
            ]),
        ]);
    }

    private static function transformStatusArray(array $statuses): array
    {
        return array_map(function (string $code) {
            return [
                'code' => $code,
                'label' => CampaignHistoryStatusEnum::LABELS[$code],
            ];
        }, $statuses);
    }
}
