<?php

namespace App\Normalizer;

use ApiPlatform\Exception\ItemNotFoundException;
use App\Entity\ElectedRepresentative\Mandate;
use App\Repository\ElectedRepresentative\MandateRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ElectedRepresentativeMandateFromIdDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly MandateRepository $repository)
    {
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if ($mandate = $this->repository->find($data)) {
            return $mandate;
        }

        throw new ItemNotFoundException();
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return Mandate::class === $type && \is_int($data);
    }
}
