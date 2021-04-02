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

    private const ALREADY_CALLED = 'ZONE_DENORMALIZER_ALREADY_CALLED';

    private $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        return $this->zoneRepository->findOneByUuid($data);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && Zone::class === $type && Uuid::isValid($data);
    }
}
