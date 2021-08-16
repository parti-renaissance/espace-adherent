<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Repository\AdherentMessage\Segment\AudienceSegmentRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceSegmentFromUuidDenormalizer implements DenormalizerInterface
{
    private $repository;

    public function __construct(AudienceSegmentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var AudienceSegment $audienceSegment */
        if ($segment = $this->repository->findOneByUuid($data)) {
            return $segment;
        }

        throw new ItemNotFoundException();
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return AudienceSegment::class === $type
            && \is_string($data)
            && Uuid::isValid($data);
    }
}
