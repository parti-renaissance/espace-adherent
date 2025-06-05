<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    public function __construct(
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($urlGenerator);
    }

    protected function getClassName(): string
    {
        return Event::class;
    }

    /** @param Event $object */
    protected function isNational(object $object): bool
    {
        return $object->isNational();
    }

    /** @param Event $object */
    protected function getTitle(object $object): string
    {
        return $object->getName();
    }

    /** @param Event $object */
    protected function getIdentifier(object $object): string
    {
        return $object->getSlug() ?? parent::getIdentifier($object);
    }

    /** @param Event $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getDescription();
    }

    /** @param Event $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param Event $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->isNational() ? null : $object->getAuthor();
    }

    /** @param Event $object */
    protected function getCategory(object $object): ?string
    {
        return $object->getCategoryName();
    }

    /** @param Event $object */
    protected function getAddress(object $object): ?string
    {
        return $object->getInlineFormattedAddress();
    }

    /** @param Event $object */
    protected function getPostAddress(object $object): ?array
    {
        return $object->getPostAddress()?->toArray();
    }

    /** @param Event $object */
    protected function getBeginAt(object $object): ?\DateTime
    {
        return $object->getLocalBeginAt();
    }

    /** @param Event $object */
    protected function getMode(object $object): ?string
    {
        return $object->getMode();
    }

    /** @param Event $object */
    protected function getVisibility(object $object): ?string
    {
        return $object->visibility->value;
    }

    /** @param Event $object */
    protected function getFinishAt(object $object): ?\DateTime
    {
        return $object->getLocalFinishAt();
    }

    /** @param Event $object */
    protected function getTimeZone(object $object): ?string
    {
        return $object->getTimeZone();
    }

    /** @param Event $object */
    protected function getImage(object $object): ?array
    {
        return $object->hasImageName() ?
            [
                'url' => $this->urlGenerator->generate(
                    'asset_url',
                    ['path' => $object->getImagePath()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'width' => $object->getImageWidth(),
                'height' => $object->getImageHeight(),
            ]
            : null;
    }

    /** @param Event $object */
    protected function getZoneCodes(object $object): ?array
    {
        if ($object->getZones()->isEmpty()) {
            return null;
        }

        $zonesCodes = [];

        foreach ($object->getZones() as $zone) {
            $zonesCodes[] = $this->buildZoneCodes($zone);
        }

        return array_values(array_unique(array_merge(...$zonesCodes)));
    }

    /** @param Event $object */
    protected function getParticipantsCount(object $object): ?int
    {
        return $object->getParticipantsCount();
    }

    protected function getCommitteeUuid(object $object): ?string
    {
        return $object->getCommitteeUuid();
    }

    protected function getAgoraUuid(object $object): ?string
    {
        return $object->agora?->getUuid()->toString();
    }

    /** @param Event $object */
    protected function getLiveUrl(mixed $object): ?string
    {
        return $object->liveUrl;
    }

    /** @param Event $object */
    protected function getAdherentIds(object $object): ?array
    {
        return array_map(
            static fn (Adherent $adherent) => $adherent->getId(),
            $this->eventRegistrationRepository->findAdherentMembersOfEvent($object)
        );
    }
}
