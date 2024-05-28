<?php

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Entity\Adherent;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Action\ActionParticipantRepository;
use Symfony\Component\Security\Core\Security;

class ActionProcessor extends AbstractFeedProcessor
{
    private ?Adherent $currentUser = null;

    public function __construct(
        private readonly Security $security,
        private readonly ActionParticipantRepository $actionParticipantRepository,
    ) {
    }

    public function process(array $item, array &$context): array
    {
        $item['user_registered_at'] = $this->actionParticipantRepository->findAdherentRegistration(
            $item['objectID'],
            $this->getCurrentUser()->getUuidAsString()
        )?->getCreatedAt();

        return $item;
    }

    public function supports(array $item): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::ACTION;
    }

    private function getCurrentUser(): Adherent
    {
        if ($this->currentUser) {
            return $this->currentUser;
        }

        return $this->currentUser = $this->security->getUser();
    }
}
