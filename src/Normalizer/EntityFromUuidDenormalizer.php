<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EntityFromUuidDenormalizer implements DenormalizerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if ($object = $this->entityManager->getRepository($type)->findOneBy(['uuid' => $data])) {
            return $object;
        }

        throw new ItemNotFoundException(sprintf('Entity "%s" with UUID "%s" not found', $type, $data));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return \is_string($data)
            && Uuid::isValid($data)
            && str_contains($type, 'App\\Entity\\')
        ;
    }
}
