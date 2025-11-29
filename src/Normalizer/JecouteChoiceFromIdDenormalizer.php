<?php

declare(strict_types=1);

namespace App\Normalizer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Entity\Jecoute\Choice;
use App\Repository\Jecoute\ChoiceRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class JecouteChoiceFromIdDenormalizer implements DenormalizerInterface
{
    public function __construct(private readonly ChoiceRepository $repository)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var Choice $choice */
        if ($choice = $this->repository->find((int) $data)) {
            return $choice;
        }

        throw new ItemNotFoundException();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Choice::class => true,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return Choice::class === $type && is_numeric($data);
    }
}
