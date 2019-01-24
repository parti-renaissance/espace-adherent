<?php

namespace AppBundle\Normalizer;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Repository\IdeaRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdeaNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $tokenStorage;
    private $ideaRepository;

    public function __construct(
        NormalizerInterface $normalizer,
        IdeaRepository $ideaRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
        $this->ideaRepository = $ideaRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('idea_list_read', $context['groups']) || \in_array('idea_read', $context['groups'])) {
            if (\in_array('idea_list_read', $context['groups'])) {
                $data['contributors_count'] = $this->ideaRepository->countContributors($object);
                $data['comments_count'] = $this->ideaRepository->countAllComments($object);
            }

            $data['votes_count'] = array_merge(
                $this->ideaRepository->countVotesByType($object),
                ['total' => $data['votes_count']]
            );

            if (\is_object($loggedUser = $this->tokenStorage->getToken()->getUser())) {
                $data['votes_count']['my_votes'] = $this->ideaRepository->getAdherentVotesForIdea($object, $loggedUser);
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Idea;
    }
}
