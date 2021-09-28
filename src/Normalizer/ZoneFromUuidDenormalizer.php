<?php

namespace App\Normalizer;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ZoneFromUuidDenormalizer implements DenormalizerInterface
{
    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return $this->zoneRepository->findOneByUuid($data);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return Zone::class === $type && \is_string($data) && Uuid::isValid($data);
    }
}
