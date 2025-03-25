<?php

namespace App\Normalizer;

use ApiPlatform\Validator\Exception\ConstraintViolationListAwareExceptionInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationListNormalizer implements NormalizerInterface
{
    public function __construct(private readonly NameConverterInterface $nameConverter)
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if ($object instanceof ConstraintViolationListAwareExceptionInterface) {
            $object = $object->getConstraintViolationList();
        }

        $violations = [];

        foreach ($object as $violation) {
            $propertyPath = $this->nameConverter ? $this->nameConverter->normalize($violation->getPropertyPath(), null, $format, $context) : $violation->getPropertyPath();

            $violations[] = [
                'propertyPath' => $propertyPath,
                'message' => $violation->getMessage(),
            ];
        }

        $result = [
            'status' => 'error',
            'message' => 'Validation Failed',
        ];

        return $result + ['violations' => $violations];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ConstraintViolationListInterface::class => true,
            ConstraintViolationListAwareExceptionInterface::class => true,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ConstraintViolationListInterface || $data instanceof ConstraintViolationListAwareExceptionInterface;
    }
}
