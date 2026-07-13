<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Poll\PollRepository;
use App\Repository\Poll\VoteRepository;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;

class PollProcessor extends AbstractFeedProcessor
{
    public function __construct(
        private readonly PollRepository $pollRepository,
        private readonly VoteRepository $voteRepository,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function process(array $item, Adherent $user): array
    {
        $poll = $this->pollRepository->findOneByUuid($item['objectID']);

        if (null === $poll) {
            return $item;
        }

        $item['user_registered_at'] = $this->voteRepository->findAdherentVote(Uuid::fromString($item['objectID']), $user)?->getCreatedAt();

        $item['poll'] = $this->normalizer->normalize($poll, null, [
            'groups' => ['poll_read'],
            PrivatePublicContextBuilder::CONTEXT_KEY => PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER,
        ]);

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::POLL;
    }
}
