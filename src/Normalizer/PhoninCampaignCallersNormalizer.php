<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PhoninCampaignCallersNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const PHONING_CAMPAIGN_CALLERS_ALREADY_CALLED = 'PHONING_CAMPAIGN_CALLERS_NORMALIZER_ALREADY_CALLED';

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::PHONING_CAMPAIGN_CALLERS_ALREADY_CALLED] = true;

        array_walk($object, function (&$adherent, $key) {
            $adherent['position'] = ++$key;
            unset($adherent['id']);
        });

        return $object;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::PHONING_CAMPAIGN_CALLERS_ALREADY_CALLED])
            && \in_array('phoning_campaign_callers_with_scores', $context['groups'] ?? [])
        ;
    }
}
