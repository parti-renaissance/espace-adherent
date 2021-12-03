<?php

namespace App\Normalizer;

use App\Entity\Jecoute\News;
use App\Jecoute\JecouteSpaceEnum;
use App\Jecoute\NewsTitlePrefix;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
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

    private const NORMALIZER_ALREADY_CALLED = 'NEWS_NORMALIZER_ALREADY_CALLED';
    private const DENORMALIZER_ALREADY_CALLED = 'NEWS_DENORMALIZER_ALREADY_CALLED';

    private RequestStack $requestStack;
    private AuthorizationChecker $authorizationChecker;
    private $newsTitlePrefix;

    public function __construct(
        RequestStack $requestStack,
        AuthorizationChecker $authorizationChecker,
        NewsTitlePrefix $newsTitlePrefix
    ) {
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->newsTitlePrefix = $newsTitlePrefix;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::NORMALIZER_ALREADY_CALLED] = true;

        $news = $this->normalizer->normalize($object, $format, $context);

        $news['title'] = $this->newsTitlePrefix->prefixTitle($object);

        if (\in_array('jecoute_news_read_dc', $context['groups'] ?? [], true)) {
            $news['creator'] = $object->getAuthorFullName();
        }

        return $news;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::NORMALIZER_ALREADY_CALLED])
            && $data instanceof News
            && 0 !== \count(array_intersect(['jecoute_news_read', 'jecoute_news_read_dc'], $context['groups'] ?? []))
        ;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::DENORMALIZER_ALREADY_CALLED] = true;

        if (isset($context['item_operation_name']) && 'put' === $context['item_operation_name']) {
            unset($data['zone']);
        }

        /** @var News $news */
        $news = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (!$news->getId()) {
            $scope = $this->authorizationChecker->getScope($this->requestStack->getMasterRequest());
            if (ScopeEnum::REFERENT === $scope) {
                $news->setSpace(JecouteSpaceEnum::REFERENT_SPACE);
            }
        }

        $this->newsTitlePrefix->removePrefix($news);

        return $news;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::DENORMALIZER_ALREADY_CALLED]) && News::class === $type;
    }
}
