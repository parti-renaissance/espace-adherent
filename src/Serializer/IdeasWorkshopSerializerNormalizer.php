<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\ThreadCommentRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class IdeasWorkshopSerializerNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $adherentRepository;
    private $threadCommentRepository;

    public function __construct(
        ObjectNormalizer $normalizer,
        AdherentRepository $adherentRepository,
        ThreadCommentRepository $threadCommentRepository
    ) {
        $this->normalizer = $normalizer;
        $this->adherentRepository = $adherentRepository;
        $this->threadCommentRepository = $threadCommentRepository;
    }

    public function normalize($idea, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($idea, $format, $context);

        if (\in_array('idea_list_read', $context['groups'])) {
            $data['contributors_count'] = $this->adherentRepository->countIdeaContributors($idea);
            $data['comments_count'] = $this->threadCommentRepository->countThreadComments($idea);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Idea;
    }
}
