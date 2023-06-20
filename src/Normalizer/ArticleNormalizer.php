<?php

namespace App\Normalizer;

use App\Entity\Article;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ArticleNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ARTICLE_NORMALIZER_ALREADY_CALLED';

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Article $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $data['media'] = $object->getMedia()
            ? [
                'type' => $object->getMedia()->isVideo() ? 'video' : 'image',
                'path' => $this->urlGenerator->generate(
                    'asset_url',
                    ['path' => $object->getMedia()->getPathWithDirectory()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
            : null;

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && $data instanceof Article
            && 0 !== \count(array_intersect(['article_list_read', 'article_read'], $context['groups'] ?? []));
    }
}
