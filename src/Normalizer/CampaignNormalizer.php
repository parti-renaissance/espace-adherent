<?php

namespace App\Normalizer;

use App\Entity\Phoning\Campaign;
use App\Repository\Phoning\CampaignHistoryRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const CAMPAIGN_ALREADY_CALLED = 'CAMPAIGN_NORMALIZER_ALREADY_CALLED';

    private CampaignHistoryRepository $campaignHistoryRepository;

    public function __construct(CampaignHistoryRepository $campaignHistoryRepository)
    {
        $this->campaignHistoryRepository = $campaignHistoryRepository;
    }

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::CAMPAIGN_ALREADY_CALLED] = true;

        $campaign = $this->normalizer->normalize($object, $format, $context);

        $campaign['nb_calls'] = $object->getCampaignHistoriesCount();
        $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurvey()->count();

        $stats = [
            'nb_un_join' => 0,
            'nb_un_subscribe' => 0,
            'to_remind' => 0,
            'not_respond' => 0,
            'nb_failed' => 0,
            'average_calling_time' => 0,
        ];

        if (isset($context['item_operation_name']) && \in_array($context['item_operation_name'], ['get', 'put'])) {
            $stats = [
                'nb_un_join' => $object->getCampaignHistoriesToUnjoin()->count(),
                'nb_un_subscribe' => $object->getCampaignHistoriesToUnsubscribe()->count(),
                'to_remind' => $object->getCampaignHistoriesToRemind()->count(),
                'not_respond' => $object->getCampaignHistoriesNotRespond()->count(),
                'nb_failed' => $object->getCampaignHistoriesFailed()->count(),
                'average_calling_time' => $this->campaignHistoryRepository->findPhoningCampaignAverageCallingTime($object),
            ];

            $campaign = array_merge($campaign, $stats);
        }

        if (isset($context['collection_operation_name']) && 'post' === $context['collection_operation_name']) {
            $campaign = array_merge($campaign, $stats);
        }

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
            && 0 !== \count(array_intersect(['phoning_campaign_list', 'phoning_campaign_read'], $context['groups'] ?? []))
        ;
    }
}
