<?php

namespace App\Normalizer\Phoning;

use App\Entity\Phoning\Campaign;
use App\Repository\Phoning\CampaignHistoryRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AppendCampaignStatsNormalizer implements NormalizerInterface, NormalizerAwareInterface
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
        $campaign['nb_adherents_called'] = $this->campaignHistoryRepository->countPhoningCampaignAdherentsCalled($object);

        if (($context['operation_name'] ?? null) === '_api_/v3/phoning_campaigns/{uuid}_get') {
            $campaign['nb_un_join'] = $object->getCampaignHistoriesToUnjoin()->count();
            $campaign['nb_un_subscribe'] = $object->getCampaignHistoriesToUnsubscribe()->count();
            $campaign['to_remind'] = $object->getCampaignHistoriesToRemind()->count();
            $campaign['not_respond'] = $object->getCampaignHistoriesNotRespond()->count();
            $campaign['nb_failed'] = $object->getCampaignHistoriesFailed()->count();
            $campaign['average_calling_time'] = $this->campaignHistoryRepository->findPhoningCampaignAverageCallingTime($object);
        }

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
            && 0 !== \count(array_intersect(['phoning_campaign_list', 'phoning_campaign_read'], $context['groups'] ?? []));
    }
}
