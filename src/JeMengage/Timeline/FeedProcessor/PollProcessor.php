<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Poll\PollRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;

class PollProcessor extends AbstractFeedProcessor
{
    public function __construct(
        private readonly PollRepository $pollRepository,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function process(array $item, Adherent $user): array
    {
        $identifier = $item['identifier'] ?? null;

        if (!\is_string($identifier) || !Uuid::isValid($identifier) || !$poll = $this->pollRepository->findOnePublishedByUuid($identifier)) {
            return $item;
        }

        $item['poll'] = $this->normalizer->normalize($poll, 'json', [
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
