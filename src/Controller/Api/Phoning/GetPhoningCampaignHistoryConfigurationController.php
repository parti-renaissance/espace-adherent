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
 * @Route("/v3/phoning_campaign_histories/{uuid}/configuration", requirements={"uuid": "%pattern_uuid%"}, name="api_get_phoning_campaign_history_configuration", methods={"GET"})
 *
 * @Security("is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')")
 */
class GetPhoningCampaignHistoryConfigurationController extends AbstractController
{
    public function __invoke(
        CampaignHistory $campaignHistory,
        UserInterface $connectedAdherent,
        AdherentRepository $adherentRepository
    ): JsonResponse {
        return $this->json([
            'call_status' => [
                'finished' => self::transformStatusArray(CampaignHistoryStatusEnum::LABEL_FINISHED_STATUS),
                'interrupted' => self::transformStatusArray(CampaignHistoryStatusEnum::LABEL_INTERRUPTED_STATUS),
            ],
            'satisfaction_questions' => array_merge(!$campaignHistory->getAdherent()->isEmailUnsubscribed() ? [[
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
        ], Response::HTTP_OK);
    }

    private static function transformStatusArray(array $arrStatus): array
    {
        array_walk($arrStatus, function (&$value, string $code) {
            $value = [
                'code' => $code,
                'value' => $value,
            ];
        });

        return array_values($arrStatus);
    }
}
