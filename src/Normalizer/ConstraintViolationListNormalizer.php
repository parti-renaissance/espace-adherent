<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationListNormalizer implements NormalizerInterface
{
    public function __construct(private readonly NameConverterInterface $nameConverter)
    {
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $violations = [];

        foreach ($object as $violation) {
            $propertyPath = $this->nameConverter ? $this->nameConverter->normalize($violation->getPropertyPath(), null, $format, $context) : $violation->getPropertyPath();

            $violations[] = [
                'property' => $propertyPath,
                'message' => $violation->getMessage(),
            ];
        }

        $result = [
            'status' => 'error',
            'message' => 'Validation Failed',
        ];

        return $result + ['violations' => $violations];
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof ConstraintViolationListInterface;
    }
}
