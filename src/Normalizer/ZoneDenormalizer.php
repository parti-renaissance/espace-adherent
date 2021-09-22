<?php

namespace App\Normalizer;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ZoneDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->zoneRepository->findOneByUuid($data);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return Zone::class === $type && \is_string($data) && Uuid::isValid($data);
    }
}
