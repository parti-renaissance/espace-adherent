<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Repository\Poll\VoteRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PollNormalizer implements NormalizerInterface
{
    public const CONTEXT_NOW = 'poll_now';
    public const CONTEXT_ADHERENT = 'poll_adherent';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly VoteRepository $voteRepository,
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        \assert($data instanceof Poll);

        $now = $context[self::CONTEXT_NOW] ?? new \DateTimeImmutable();
        $adherent = $context[self::CONTEXT_ADHERENT] ?? null;

        $normalized = [
            'uuid' => $data->getUuid()->toRfc4122(),
            'question' => $data->getQuestion(),
            'start_at' => $this->formatDate($data->getStartAt()),
            'finish_at' => $this->formatDate($data->getFinishAt()),
            'result_display_end_at' => $this->formatDate($data->getResultDisplayEndAt()),
            'description' => $data->getDescription(),
            'state' => $data->getState($now),
            'choices' => array_map(
                fn (Choice $choice): array => $this->normalizeChoice($choice),
                $data->getChoices()->toArray()
            ),
            'participant_count_threshold' => $data->getParticipantCountThreshold(),
            'result_display_mode' => $data->getResultDisplayMode()->value,
        ];

        $hasVoted = $adherent instanceof Adherent && $this->voteRepository->hasVoted($data, $adherent);

        if ($data->canDisplayResult($now, $hasVoted)) {
            $normalized['participant_count'] = $this->voteRepository->countParticipants($data);
            $normalized['participants'] = $this->normalizeParticipants($data);
            $normalized['result'] = $this->normalizeResult($data);
        }

        return $normalized;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Poll;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Poll::class => true,
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
                'first_name' => $adherent->getFirstName(),
                'image_url' => $this->urlGenerator->generate(
                    'asset_url',
                    ['path' => $adherent->getImagePath()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
            $this->voteRepository->findLatestVotersWithImage($poll)
        );
    }

    private function normalizeResult(Poll $poll): array
    {
        $result = $poll->getResult();

        return [
            'total' => $result['total'],
            'choices' => array_map(
                fn (array $choiceResult): array => [
                    'choice' => $this->normalizeChoice($choiceResult['choice']),
                    'count' => $choiceResult['count'],
                    'percentage' => $choiceResult['percentage'],
                ],
                $result['choices']
            ),
        ];
    }

    private function formatDate(?\DateTimeInterface $date): ?string
    {
        return $date?->format(\DateTimeInterface::ATOM);
    }
}
