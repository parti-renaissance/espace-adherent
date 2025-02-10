<?php

namespace App\JeMengage\Timeline\FeedProcessor;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Event\EventCleaner;
use App\Event\EventVisibilityEnum;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\EventRegistrationRepository;
use App\Security\Voter\Event\CanManageEventVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class EventProcessor extends AbstractFeedProcessor
{
    public function __construct(
        private readonly EventCleaner $eventCleaner,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function process(array $item, Adherent $user): array
    {
        $item = $this->appendEventManagerData($item, $user);
        $item = $this->appendEventRegistrationDate($item, $user);
        $item = $this->cleanEventDataIfNeed($item, $user);

        return $item;
    }

    public function supports(array $item, Adherent $user): bool
    {
        return ($item['type'] ?? null) === TimelineFeedTypeEnum::EVENT;
    }

    private function cleanEventDataIfNeed(array $item, Adherent $user): array
    {
        $needClean =
            true !== $item['editable']
            && (
                EventVisibilityEnum::isForAdherent($visibility = $item['visibility'] ?? EventVisibilityEnum::ADHERENT_DUES->value)
                && (
                    (EventVisibilityEnum::ADHERENT->value === $visibility && !$user->hasTag(TagEnum::ADHERENT))
                    || (EventVisibilityEnum::ADHERENT_DUES->value === $visibility && !$user->hasTag(TagEnum::getAdherentYearTag()))
                )
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

    private function appendEventManagerData(array $item, Adherent $user): array
    {
        $item['editable'] = $this->authorizationChecker->isGranted(CanManageEventVoter::CAN_MANAGE_EVENT_ITEM, [
            'instance' => $item['author']['instance'] ?? null,
            'zones' => array_map(fn (string $code) => explode('_', $code, 2)[1], $item['zone_codes'] ?? []),
            'committee_uuid' => $item['committee_uuid'] ?? null,
            'is_national' => $item['is_national'] ?? false,
        ]);

        if ($item['editable']) {
            $item['edit_link'] = $this->loginLinkHandler->createLoginLink($user, targetPath: '/cadre?state='.urlencode('/evenements/'.$item['objectID'].'?scope='.($item['author']['scope'] ?? null)))->getUrl();
        }

        return $item;
    }
}
