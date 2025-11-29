<?php

declare(strict_types=1);

namespace App\Normalizer\Phoning;

use App\Entity\Phoning\Campaign;
use App\Repository\Phoning\CampaignHistoryRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AppendCampaignStatsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly CampaignHistoryRepository $campaignHistoryRepository)
    {
    }

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $campaign = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

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

    public function getSupportedTypes(?string $format): array
    {
        return [
            Campaign::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof Campaign
            && 0 !== \count(array_intersect(['phoning_campaign_list', 'phoning_campaign_read'], $context['groups'] ?? []));
    }
}
