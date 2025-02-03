<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return BaseEvent::class;
    }

    /** @param BaseEvent $object */
    protected function isNational(object $object): bool
    {
        return $object->national;
    }

    /** @param BaseEvent $object */
    protected function getTitle(object $object): string
    {
        return $object->getName();
    }

    /** @param BaseEvent $object */
    protected function getIdentifier(object $object): string
    {
        return $object->getSlug() ?? parent::getIdentifier($object);
    }

    /** @param BaseEvent $object */
    protected function getDescription(object $object): ?string
    {
        return $object->getDescription();
    }

    /** @param BaseEvent $object */
    protected function isLocal(object $object): bool
    {
        return true;
    }

    /** @param BaseEvent $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param BaseEvent $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->national ? null : $object->getAuthor();
    }

    /** @param BaseEvent $object */
    protected function getCategory(object $object): ?string
    {
        return $object->getCategoryName();
    }

    /** @param BaseEvent $object */
    protected function getAddress(object $object): ?string
    {
        return $object->getInlineFormattedAddress();
    }

    /** @param BaseEvent $object */
    protected function getPostAddress(object $object): ?array
    {
        return $object->getPostAddress()?->toArray();
    }

    /** @param BaseEvent $object */
    protected function getBeginAt(object $object): ?\DateTime
    {
        return $object->getLocalBeginAt();
    }

    /** @param BaseEvent $object */
    protected function getMode(object $object): ?string
    {
        return $object->getMode();
    }

    /** @param BaseEvent $object */
    protected function getVisibility(object $object): ?string
    {
        return $object->visibility->value;
    }

    /** @param BaseEvent $object */
    protected function getFinishAt(object $object): ?\DateTime
    {
        return $object->getLocalFinishAt();
    }

    /** @param BaseEvent $object */
    protected function getTimeZone(object $object): ?string
    {
        return $object->getTimeZone();
    }

    /** @param BaseEvent $object */
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

    /** @param BaseEvent $object */
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

    /** @param BaseEvent $object */
    protected function getParticipantsCount(object $object): ?int
    {
        return $object->getParticipantsCount();
    }

    protected function getCommitteeUuid(object $object): ?string
    {
        return $object instanceof CommitteeEvent ? $object->getCommitteeUuid() : null;
    }
}
