<?php

namespace App\Normalizer;

use App\Entity\GeneralConvention\GeneralConvention;
use App\Repository\AdherentRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GeneralConventionDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly AdherentRepository $adherentRepository)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var GeneralConvention $object */
        $object = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if ($data['reporter']) {
            $object->reporter = $this->adherentRepository->findOneByEmail($data['reporter']);
        }

        return $object;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GeneralConvention::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && GeneralConvention::class === $type;
    }
}
