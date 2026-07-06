<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Utils\DateUtils;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer as BaseDateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Workaround for https://github.com/symfony/symfony/issues/27824
 */
class DateTimeNormalizer implements DenormalizerInterface, NormalizerInterface
{
    private BaseDateTimeNormalizer $decorated;

    public function __construct(BaseDateTimeNormalizer $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return $this->decorated->normalize($object, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        if (\is_string($data)) {
            $date = DateUtils::createValidDate($data);

            if (\DateTimeImmutable::class === $class) {
                return \DateTimeImmutable::createFromInterface($date);
            }

            return $date;
        }

        try {
            return $this->decorated->denormalize($data, $class, $format, $context);
        } catch (NotNormalizableValueException $e) {
            throw new BadRequestHttpException(\sprintf('Invalid date value: "%s".', $data));
        }
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }
}
