<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Repository\IdeaRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdeasWorkshopSerializerNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $ideaRepository;

    public function __construct(NormalizerInterface $normalizer, IdeaRepository $ideaRepository)
    {
        $this->normalizer = $normalizer;

        $this->ideaRepository = $ideaRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('idea_list_read', $context['groups'])) {
            $data['contributors_count'] = $this->ideaRepository->countIdeaContributors($object);
            $data['comments_count'] = $this->ideaRepository->countThreadComments($object);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Idea;
    }
}
