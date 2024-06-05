<?php

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Event\EventCleaner;
use App\Event\EventVisibilityEnum;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\EventRegistrationRepository;

class EventProcessor extends AbstractFeedProcessor
{
    public function __construct(
        private readonly EventCleaner $eventCleaner,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
    ) {
    }

    public function process(array $item, Adherent $user): array
    {
        $item = $this->cleanEventDataIfNeed($item, $user);
        $item = $this->appendEventRegistrationDate($item, $user);

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::EVENT;
    }

    private function cleanEventDataIfNeed(array $item, Adherent $user): array
    {
        $needClean = EventVisibilityEnum::isForAdherent($visibility = $item['visibility'] ?? EventVisibilityEnum::ADHERENT_DUES->value)
            && (
                (EventVisibilityEnum::ADHERENT->value === $visibility && !$user->hasTag(TagEnum::ADHERENT))
                || (EventVisibilityEnum::ADHERENT_DUES->value === $visibility && !$user->hasTag(TagEnum::getAdherentYearTag()))
            );

        if ($needClean) {
            $item = $this->eventCleaner->cleanEventData($item);
            $item['object_state'] = 'partial';
        } else {
            $item['object_state'] = 'full';
        }

        return $item;
    }

    private function appendEventRegistrationDate(array $item, Adherent $user): array
    {
        $item['user_registered_at'] = $this->eventRegistrationRepository->findAdherentRegistration(
            $item['objectID'],
            $user->getUuidAsString()
        )?->getCreatedAt();

        return $item;
    }
}
