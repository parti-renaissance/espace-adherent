<?php

namespace App\Normalizer;

use App\Entity\Jecoute\News;
use App\Jecoute\NewsTitlePrefix;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteNewsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const NEWS_ALREADY_CALLED = 'news_normalizer';

    private $newsTitlePrefix;

    public function __construct(NewsTitlePrefix $newsTitlePrefix)
    {
        $this->newsTitlePrefix = $newsTitlePrefix;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::NEWS_ALREADY_CALLED] = true;

        $news = $this->normalizer->normalize($object, $format, $context);

        $news['title'] = $this->newsTitlePrefix->prefixTitle($object);

        return $news;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::NEWS_ALREADY_CALLED])
            && $data instanceof News
            && 0 !== \count(array_intersect(['jecoute_news_read', 'jecoute_news_read_dc'], $context['groups'] ?? []))
        ;
    }
}
