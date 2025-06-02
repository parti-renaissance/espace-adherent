<?php

namespace App\Normalizer\Pap;

use App\Address\AddressInterface;
use App\Entity\Pap\CampaignHistory;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CampaignHistoryDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var CampaignHistory $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
        if (!$data->getBeginAt()) {
            $data->setBeginAt(new \DateTime());
        } elseif (AddressInterface::DEFAULT_TIME_ZONE !== $data->getBeginAt()->getTimezone()->getName()) {
            $data->setBeginAt((clone $data->getBeginAt())->setTimezone(new \DateTimeZone(AddressInterface::DEFAULT_TIME_ZONE)));
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            CampaignHistory::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && CampaignHistory::class === $type;
    }
}
