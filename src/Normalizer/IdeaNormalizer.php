<?php

namespace App\Normalizer;

use App\Entity\IdeasWorkshop\Idea;
use App\Repository\IdeasWorkshop\IdeaRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdeaNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'IDEA_NORMALIZER_ALREADY_CALLED';

    private $ideaRepository;
    private $security;

    public function __construct(IdeaRepository $ideaRepository, Security $security)
    {
        $this->security = $security;
        $this->ideaRepository = $ideaRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('idea_list_read', $context['groups']) || \in_array('idea_read', $context['groups'])) {
            $data['votes_count'] = array_merge(
                $this->ideaRepository->countVotesByType($object),
                ['total' => $data['votes_count']]
            );

            if ($loggedUser = $this->security->getUser()) {
                $data['votes_count']['my_votes'] = $this->ideaRepository->getAdherentVotesForIdea($object, $loggedUser);
            } else {
                $loggedUser = null;
            }

            if (\in_array('idea_list_read', $context['groups'])) {
                $countContributors = $this->ideaRepository->countContributors($object, $loggedUser);
                $data['contributors_count'] = $countContributors['count'];
                if ($loggedUser) {
                    $data['contributed_by_me'] = $countContributors['contributed_by_me'];
                }
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Idea;
    }
}
