<?php

declare(strict_types=1);

namespace App\Normalizer;

use ApiPlatform\Validator\Exception\ConstraintViolationListAwareExceptionInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;
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

            $entry = [
                'propertyPath' => $propertyPath,
                'message' => $violation->getMessage(),
            ];

            $code = $violation->getCode();
            if (null !== $code && !Uuid::isValid($code)) {
                $entry['code'] = $code;
                if ([] !== ($parameters = $violation->getParameters())) {
                    $entry['parameters'] = $parameters;
                }
            }

            $violations[] = $entry;
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
