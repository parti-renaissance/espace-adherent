<?php

namespace App\Normalizer;

use App\Entity\CommitteeElection;
use App\Repository\VotingPlatform\ElectionRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CommitteeElectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'COMMITTEE_ELECTION_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly ElectionRepository $electionRepository)
    {
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var CommitteeElection $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['voters_count'] = $data['votes_count'] = null;

        if ($object->getDesignation()->electionCreationDate <= new \DateTime()) {
            if ($election = $this->electionRepository->findOneForCommittee($object->getCommittee(), $object->getDesignation())) {
                $detailsByPool = current($this->electionRepository->getSingleAggregatedData($election->getCurrentRound()));

                $data['voters_count'] = $detailsByPool['voters_count'] ?? null;

                if ($object->getDesignation()->isVotePeriodStarted()) {
                    $data['votes_count'] = $detailsByPool['votes_count'] ?? null;
                }
            }
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return !isset($context[self::ALREADY_CALLED])
            && $data instanceof CommitteeElection
            && \in_array('committee_election:read', $context['groups'] ?? []);
    }
}
