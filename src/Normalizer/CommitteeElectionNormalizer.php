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

    public function __construct(private readonly ElectionRepository $electionRepository)
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        /** @var CommitteeElection $object */
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

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

    public function getSupportedTypes(?string $format): array
    {
        return [
            CommitteeElection::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && $data instanceof CommitteeElection
            && \in_array('committee_election:read', $context['groups'] ?? []);
    }
}
