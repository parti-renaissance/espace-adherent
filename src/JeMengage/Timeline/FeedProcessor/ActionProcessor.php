<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Action\ActionParticipantRepository;

class ActionProcessor extends AbstractFeedProcessor
{
    public function __construct(private readonly ActionParticipantRepository $actionParticipantRepository)
    {
    }

    public function process(array $item, Adherent $user): array
    {
        $item['user_registered_at'] = $this->actionParticipantRepository->findAdherentRegistration(
            $item['objectID'],
            $user->getUuidAsString()
        )?->getCreatedAt();

        $item['editable'] = ($item['author']['uuid'] ?? '') === $user->getUuidAsString();

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::ACTION;
    }
}
