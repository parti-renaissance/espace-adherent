<?php

namespace App\Normalizer;

use App\Entity\Phoning\Campaign;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const CAMPAIGN_ALREADY_CALLED = 'CAMPAIGN_NORMALIZER_ALREADY_CALLED';

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::CAMPAIGN_ALREADY_CALLED] = true;

        $campaign = $this->normalizer->normalize($object, $format, $context);

        $campaign['nb_calls'] = $object->getCampaignHistoriesCount();
        $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurvey()->count();

        if (isset($context['item_operation_name']) && 'get' === $context['item_operation_name']) {
            $campaign['nb_un_join'] = $object->getCampaignHistoriesToUnjoin()->count();
            $campaign['nb_un_subscribe'] = $object->getCampaignHistoriesToUnsubscribe()->count();
            $campaign['to_remind'] = $object->getCampaignHistoriesToRemind()->count();
            $campaign['not_respond'] = $object->getCampaignHistoriesNotRespond()->count();
            $campaign['nb_failed'] = $object->getCampaignHistoriesFailed()->count();
        }

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
            && (\in_array('phoning_campaign_list', $context['groups'] ?? [])
                || \in_array('phoning_campaign_read', $context['groups'] ?? [])
            )
        ;
    }
}
