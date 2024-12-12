<?php

namespace App\Normalizer;

use ApiPlatform\Exception\ItemNotFoundException;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use App\Repository\ElectedRepresentative\PoliticalFunctionRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ElectedRepresentativePoliticalFunctionFromIdDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly PoliticalFunctionRepository $repository)
    {
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = [])
    {
        if ($function = $this->repository->find($data)) {
            return $function;
        }

        throw new ItemNotFoundException();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            PoliticalFunction::class => true,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return PoliticalFunction::class === $type && \is_int($data);
    }
}
