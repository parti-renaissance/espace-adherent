<?php

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Event\EventCleaner;
use App\Event\EventVisibilityEnum;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Symfony\Component\Security\Core\Security;

class EventProcessor extends AbstractFeedProcessor
{
    private const CONTEXT_CLEANER_ENABLED_KEY = 'event:cleaner_enabled';

    public function __construct(
        private readonly EventCleaner $eventCleaner,
        private readonly Security $security,
    ) {
    }

    public function process(array $item, array &$context): array
    {
        return $this->cleanEventDataIfNeed($item, $context);
    }

    public function supports(array $item): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::EVENT;
    }

    private function cleanEventDataIfNeed(array $item, array &$context): array
    {
        $needClean = $context[self::CONTEXT_CLEANER_ENABLED_KEY] ?? null;

        if (null === $needClean) {
            $user = $this->security->getUser();
            $needClean = $context[self::CONTEXT_CLEANER_ENABLED_KEY] =
                EventVisibilityEnum::isForAdherent($visibility = $item['visibility'] ?? EventVisibilityEnum::ADHERENT_DUES->value)
                && (
                    !$user instanceof Adherent
                    || (EventVisibilityEnum::ADHERENT->value === $visibility && !$user->hasTag(TagEnum::ADHERENT))
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
}
