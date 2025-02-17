<?php

namespace App\Normalizer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Entity\Event\EventCategory;
use App\Repository\EventCategoryRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EntityCategoryFromSlugDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly EventCategoryRepository $repository)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if ($object = $this->repository->findOneBy(['slug' => $data])) {
            return $object;
        }

        throw new ItemNotFoundException(\sprintf('Category "%s" with slug "%s" not found', $type, $data));
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => true,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \is_string($data) && EventCategory::class === $type;
    }
}
