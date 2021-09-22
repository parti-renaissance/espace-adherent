<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\CampaignHistory;
use App\Phoning\CampaignHistoryEngagementEnum;
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
        $adherent = $campaignHistory->getAdherent();

        return $this->json([
            'call_status' => [
                'finished' => self::transformStatusArray(CampaignHistoryStatusEnum::FINISHED_STATUS),
                'interrupted' => self::transformStatusArray(CampaignHistoryStatusEnum::INTERRUPTED_STATUS),
            ],
            'satisfaction_questions' => array_merge($adherent->isEmailUnsubscribed() ? [[
                'code' => 'need_email_renewal',
                'label' => 'Souhaitez-vous vous réabonner à nos emails ?',
                'type' => 'boolean',
            ]] : [],
                !$adherent->hasSmsSubscriptionType() ? [[
                'code' => 'need_sms_renewal',
                'label' => 'Souhaitez-vous vous réabonner à nos SMS ?',
                'type' => 'boolean',
            ]] : [],
            [
                [
                    'code' => 'postal_code_checked',
                    'label' => sprintf('Habitez-vous toujours à %s (%s) ?', $adherent->getCityName(), $adherent->getPostalCode()),
                    'type' => 'boolean',
                ],
                [
                    'code' => 'profession',
                    'label' => 'Quel est votre métier ?',
                    'type' => 'text',
                ],
                [
                    'code' => 'engagement',
                    'label' => 'Souhaitez-vous vous (re)engager sur le terrain ?',
                    'type' => 'choice',
                    'choices' => CampaignHistoryEngagementEnum::LABELS,
                ],
                [
                    'code' => 'note',
                    'label' => 'Comment s\'est passé cet appel ?',
                    'type' => 'note',
                    'values' => [1, 2, 3, 4, 5],
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
