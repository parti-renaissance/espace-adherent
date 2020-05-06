<?php

namespace App\Normalizer;

use ApiPlatform\Core\Metadata\Resource\Factory\CachedResourceMetadataFactory;
use App\Entity\IdeasWorkshop\Answer;
use App\Entity\IdeasWorkshop\Thread;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdeaAnswerNormalizer implements NormalizerInterface
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

        if (\in_array('idea_read', $context['groups'], true)) {
            $data['threads'] = [
                'total_items' => \count($data['threads']),
                'items' => \array_slice(
                    $data['threads'],
                    0,
                    $this->resourceMetadataFactory->create(Thread::class)->getAttribute('pagination_items_per_page')
                ),
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Answer;
    }
}
