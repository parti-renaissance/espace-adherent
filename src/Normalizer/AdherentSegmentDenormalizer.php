<?php

namespace AppBundle\Normalizer;

use AppBundle\Entity\AdherentSegment;
use AppBundle\Repository\AdherentRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentSegmentDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADHERENT_SEGMENT_DENORMALIZER_ALREADY_CALLED';

    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data['member_ids'] = $this->adherentRepository->findIdByUuids($data['member_ids']);

        return $this->denormalizer->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return
            AdherentSegment::class === $type
            && !empty($data['member_ids'])
            && Uuid::isValid($data['member_ids'][0])
        ;
    }
}
