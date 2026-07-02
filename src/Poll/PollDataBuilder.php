<?php

declare(strict_types=1);

namespace App\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Repository\Poll\VoteRepository;

readonly class PollDataBuilder
{
    public function __construct(private VoteRepository $voteRepository)
    {
    }

    public function build(Poll $poll, \DateTimeInterface $now, ?Adherent $adherent = null): array
    {
        $data = [
            'uuid' => $poll->getUuid()->toRfc4122(),
            'question' => $poll->getQuestion(),
            'start_at' => $this->formatDate($poll->getStartAt()),
            'finish_at' => $this->formatDate($poll->getFinishAt()),
            'result_display_end_at' => $this->formatDate($poll->getResultDisplayEndAt()),
            'description' => $poll->getDescription(),
            'choices' => array_map(
                fn (Choice $choice): array => $this->buildChoice($choice),
                $poll->getChoices()->toArray()
            ),
            'participant_count_threshold' => $poll->getParticipantCountThreshold(),
            'result_display_mode' => $poll->getResultDisplayMode()->value,
        ];

        $hasVoted = $adherent && $this->voteRepository->hasVoted($poll, $adherent);

        if ($poll->canDisplayResult($now, $hasVoted)) {
            $data['result'] = $this->buildResult($poll);
        }

        return $data;
    }

    private function buildChoice(Choice $choice): array
    {
        return [
            'uuid' => $choice->getUuid()->toRfc4122(),
            'value' => $choice->getValue(),
        ];
    }

    private function buildResult(Poll $poll): array
    {
        $result = $poll->getResult();

        return [
            'total' => $result['total'],
            'choices' => array_map(
                fn (array $choiceResult): array => [
                    'choice' => $this->buildChoice($choiceResult['choice']),
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
