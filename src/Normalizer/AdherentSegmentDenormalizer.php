<?php

namespace App\Normalizer;

use App\Entity\AdherentSegment;
use App\Repository\AdherentRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentSegmentDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly AdherentRepository $adherentRepository)
    {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        $data['member_ids'] = $this->adherentRepository->findIdByUuids($data['member_ids']);

        return $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AdherentSegment::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && AdherentSegment::class === $type
            && !empty($data['member_ids'])
            && Uuid::isValid($data['member_ids'][0]);
    }
}
