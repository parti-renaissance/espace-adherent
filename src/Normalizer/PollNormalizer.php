<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Repository\Poll\VoteRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PollNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly VoteRepository $voteRepository,
        private readonly Security $security,
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $normalized = $this->normalizer->normalize($data, $format, $context + [__CLASS__ => true]);

        $now = new \DateTimeImmutable();
        $adherent = $this->security->getUser();

        $vote = $adherent instanceof Adherent
            ? $this->voteRepository->findOneBy(['poll' => $data, 'adherent' => $adherent])
            : null;

        $hasVoted = null !== $vote;

        $normalized['has_voted'] = $hasVoted;
        $normalized['voted_choice'] = $vote?->getChoice()->getUuid()->toRfc4122();
        $normalized['voted_at'] = $vote?->getCreatedAt()->format(\DateTimeInterface::RFC3339);

        if ($data->isVotePeriodActive($now) || $data->canDisplayResult($now, $hasVoted)) {
            $normalized['participants'] = $this->normalizeParticipants($data);
        }

        if (!$data->reachesParticipantCountThreshold()) {
            unset($normalized['participant_count']);
        }

        if ($data->canDisplayPercentage($hasVoted)) {
            $normalized['result'] = $this->normalizeResult($data, $data->exceedsParticipantCountThreshold());
        }

        return $normalized;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return empty($context[__CLASS__])
            && !empty($context[PrivatePublicContextBuilder::CONTEXT_KEY])
            && \in_array('poll_read', $context['groups'] ?? [], true)
            && $data instanceof Poll;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Poll::class => false,
        ];
    }

    private function normalizeChoice(Choice $choice): array
    {
        return [
            'uuid' => $choice->getUuid()->toRfc4122(),
            'value' => $choice->getValue(),
        ];
    }

    private function normalizeParticipants(Poll $poll): array
    {
        return array_map(
            fn (Adherent $adherent): array => [
                'image_url' => $this->urlGenerator->generate(
                    'asset_url',
                    ['path' => $adherent->getImagePath()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            $this->voteRepository->findLatestVotersWithImage($poll)
        );
    }

    private function normalizeResult(Poll $poll, bool $withCount): array
    {
        $result = $poll->getResult();

        $normalized = [];

        if ($withCount) {
            $normalized['total'] = $result['total'];
        }

        $normalized['choices'] = array_map(
            function (array $choiceResult) use ($withCount): array {
                $choice = [
                    'choice' => $this->normalizeChoice($choiceResult['choice']),
                    'percentage' => $choiceResult['percentage'],
                ];

                if ($withCount) {
                    $choice['count'] = $choiceResult['count'];
                }

                return $choice;
            },
            $result['choices']
        );

        return $normalized;
    }
}
