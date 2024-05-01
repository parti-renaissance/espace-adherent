<?php

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Event\EventCleaner;
use App\Event\EventVisibilityEnum;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Security;

class EventProcessor extends AbstractFeedProcessor
{
    private const CONTEXT_CLEANER_ENABLED_KEY = 'event:cleaner_enabled';

    private ?Adherent $currentUser = null;

    public function __construct(
        private readonly EventCleaner $eventCleaner,
        private readonly Security $security,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
    ) {
    }

    public function process(array $item, array &$context): array
    {
        $item = $this->cleanEventDataIfNeed($item, $context);
        $item = $this->appendEventRegistrationDate($item);

        return $item;
    }

    public function supports(array $item): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::EVENT;
    }

    private function cleanEventDataIfNeed(array $item, array &$context): array
    {
        $needClean = $context[self::CONTEXT_CLEANER_ENABLED_KEY] ?? null;

        if (null === $needClean) {
            $user = $this->getCurrentUser();
            $needClean = $context[self::CONTEXT_CLEANER_ENABLED_KEY] =
                EventVisibilityEnum::isForAdherent($visibility = $item['visibility'] ?? EventVisibilityEnum::ADHERENT_DUES->value)
                && (
                    (EventVisibilityEnum::ADHERENT->value === $visibility && !$user->hasTag(TagEnum::ADHERENT))
                    || (EventVisibilityEnum::ADHERENT_DUES->value === $visibility && !$user->hasTag(TagEnum::getAdherentYearTag()))
                );
        }

        if ($needClean) {
            $item = $this->eventCleaner->cleanEventData($item);
            $item['object_state'] = 'partial';
        } else {
            $item['object_state'] = 'full';
        }

        return $item;
    }

    private function getCurrentUser(): Adherent
    {
        if ($this->currentUser) {
            return $this->currentUser;
        }

        return $this->currentUser = $this->security->getUser();
    }

    private function appendEventRegistrationDate(array $item): array
    {
        $item['user_registered_at'] = $this->eventRegistrationRepository->findAdherentRegistration(
            $item['objectID'],
            $this->getCurrentUser()->getUuidAsString()
        )?->getCreatedAt();

        return $item;
    }
}
