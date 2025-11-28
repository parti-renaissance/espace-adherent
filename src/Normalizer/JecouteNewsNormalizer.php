<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Jecoute\News;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class JecouteNewsNormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return [
            News::class => false,
        ];
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (isset($context['operation_name']) && '_api_/v3/jecoute/news/{uuid}_put' === $context['operation_name']) {
            unset($data['zone'], $data['committee']);
        }

        /** @var News $news */
        $news = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if (!$news->getId()) {
            $news->updateVisibility();
        }

        return $news;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && News::class === $type;
    }
}
