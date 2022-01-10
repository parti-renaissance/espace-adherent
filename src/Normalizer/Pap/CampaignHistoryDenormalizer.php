<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\CampaignHistory;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CampaignHistoryDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'PAP_CAMPAIGN_HISTORY_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var CampaignHistory $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context);
        if (!$data->getBeginAt()) {
            $data->setBeginAt(new \DateTime());
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::ALREADY_CALLED])
            && CampaignHistory::class === $type;
    }
}
