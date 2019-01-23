<?php

namespace AppBundle\Normalizer;

use ApiPlatform\Core\Metadata\Resource\Factory\CachedResourceMetadataFactory;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ThreadNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $resourceMetadataFactory;

    public function __construct(NormalizerInterface $normalizer, CachedResourceMetadataFactory $resourceMetadataFactory)
    {
        $this->normalizer = $normalizer;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('thread_list_read', $context['groups']) || \in_array('idea_read', $context['groups'])) {
            $data['comments'] = [
                'total_items' => \count($data['comments']),
                'items' => \array_slice(
                    $data['comments'],
                    0,
                    $this->resourceMetadataFactory->create(ThreadComment::class)->getAttribute('pagination_items_per_page')
                ),
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Thread;
    }
}
