<?php

namespace App\Normalizer;

use ApiPlatform\Exception\ItemNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EntityFromIdDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if ($object = $this->entityManager->getRepository($type)->find($data)) {
            return $object;
        }

        throw new ItemNotFoundException(sprintf('Entity "%s" with ID "%s" not found', $type, $data));
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return \is_int($data) && str_contains($type, 'App\\Entity\\');
    }
}
