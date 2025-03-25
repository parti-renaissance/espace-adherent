<?php

namespace App\Normalizer;

use App\Entity\Jecoute\News;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteNewsNormalizer implements NormalizerInterface, NormalizerAwareInterface, DenormalizerInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    /**
     * @param News $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $news = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (
            !\in_array('jecoute_news_read_dc', $context['groups'] ?? [], true)
            && isset($context['operation_name'])
            && '_api_/jecoute/news_get_collection' === $context['operation_name']
        ) {
            $news['text'] = $object->getCleanedCroppedText();
        }

        return $news;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            News::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof News
            && 0 !== \count(array_intersect(['jecoute_news_read', 'jecoute_news_read_dc'], $context['groups'] ?? []));
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
