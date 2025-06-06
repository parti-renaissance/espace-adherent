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
        $needClean = true;

        if (true === $item['editable']) {
            $needClean = false;
        } else {
            $visibility = EventVisibilityEnum::ADHERENT_DUES;
            if (!empty($item['visibility']) && \is_string($item['visibility'])) {
                $visibility = EventVisibilityEnum::tryFrom($item['visibility']) ?? $visibility;
            }

            if (\in_array($visibility, [EventVisibilityEnum::PUBLIC, EventVisibilityEnum::PRIVATE], true)) {
                $needClean = false;
            } else {
                $hasAccess =
                    (EventVisibilityEnum::ADHERENT === $visibility && $user->hasTag(TagEnum::ADHERENT))
                    || (EventVisibilityEnum::ADHERENT_DUES === $visibility && $user->hasTag(TagEnum::getAdherentYearTag()))
                    || (EventVisibilityEnum::isInvitation($visibility) && $this->eventRegistrationRepository->findAdherentRegistration($item['objectID'], $user->getUuidAsString(), null));

                if ($hasAccess) {
                    $needClean = false;
                }
            }
        }

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
            'agora_uuid' => $item['agora_uuid'] ?? null,
            'is_national' => $item['is_national'] ?? false,
        ]);

        if ($item['editable']) {
            $item['edit_link'] = $this->loginLinkHandler->createLoginLink($user, targetPath: '/cadre?state='.urlencode('/evenements/'.$item['objectID'].'?scope='.($item['author']['scope'] ?? null)))->getUrl();
        }

        return $item;
    }
}
