<?php

namespace App\Normalizer;

use App\Entity\Jecoute\News;
use App\Jecoute\JecouteSpaceEnum;
use App\Jecoute\NewsTitlePrefix;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\RequestStack;
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

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly NewsTitlePrefix $newsTitlePrefix,
    ) {
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $news = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $news['title'] = $this->newsTitlePrefix->prefixTitle($object);

        if (\in_array('jecoute_news_read_dc', $context['groups'] ?? [], true)) {
            $news['creator'] = $object->getAuthorFullName();
            $news['text'] = $object->getText();
        } else {
            $news['creator'] = $object->getAuthorFullNameWithRole();
            if (isset($context['operation_name']) && '_api_/jecoute/news_get_collection' === $context['operation_name']) {
                $news['text'] = $object->getCleanedCroppedText();
            }
        }

        return $news;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
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
            unset($data['zone']);
        }

        /** @var News $news */
        $news = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if (!$news->getId()) {
            $scope = $this->scopeGeneratorResolver->generate();
            $scopeCode = $scope?->getMainCode();
            $news->setSpace($scopeCode ? JecouteSpaceEnum::getByScope($scopeCode) : null);
        }

        $this->newsTitlePrefix->removePrefix($news);

        return $news;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && News::class === $type;
    }
}
