<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Poll\VoteRepository;
use Symfony\Component\Uid\Uuid;

class PollProcessor extends AbstractFeedProcessor
{
    public function __construct(private readonly VoteRepository $voteRepository)
    {
    }

    public function process(array $item, Adherent $user): array
    {
        $vote = $this->voteRepository->findAdherentVote(Uuid::fromString($item['objectID']), $user);

        $item['user_registered_at'] = $vote?->getCreatedAt();

        $item['poll'] = [
            'question' => $item['title'] ?? null,
            'has_voted' => null !== $vote,
        ];

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::POLL;
    }
}
