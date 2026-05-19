<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Action\ActionParticipantRepository;
use App\Security\Voter\CanManageActionVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ActionProcessor extends AbstractFeedProcessor
{
    public function __construct(
        private readonly ActionParticipantRepository $actionParticipantRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function process(array $item, Adherent $user): array
    {
        $item['user_registered_at'] = $this->actionParticipantRepository->findAdherentRegistration(
            $item['objectID'],
            $user->getUuidAsString()
        )?->getCreatedAt();

        $item['editable'] = $this->authorizationChecker->isGranted(CanManageActionVoter::CAN_MANAGE_ACTION_ITEM, [
            'author_uuid' => $item['author']['uuid'] ?? null,
        ]);

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::ACTION;
    }
}
