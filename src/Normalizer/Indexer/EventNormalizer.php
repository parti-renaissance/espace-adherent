<?php

namespace App\Normalizer\Indexer;

use App\Entity\Event\BaseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    protected function getClassName(): string
    {
        return BaseEvent::class;
    }

    /** @param BaseEvent $object */
    protected function getTitle(object $object): string
    {
        return $object->getName();
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
    protected function getAuthor(object $object): ?string
    {
        return $object->getAuthor() ? $object->getAuthor()->getFullName() : null;
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
    protected function getImage(object $object): ?string
    {
        return $object->hasImageName() ? $this->urlGenerator->generate(
            'asset_url',
            ['path' => $object->getImagePath()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;
    }

    /** @param BaseEvent $object */
    protected function getZoneCodes(object $object): ?array
    {
        if ($object->getZones()->isEmpty()) {
            return null;
        }

        $zonesCodes = [];

        foreach ($object->zones as $zone) {
            $zonesCodes[] = $this->buildZoneCodes($zone);
        }

        return array_values(array_unique(array_merge(...$zonesCodes)));
    }
}
