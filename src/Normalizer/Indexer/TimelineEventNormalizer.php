<?php

namespace App\Normalizer\Indexer;

use App\Entity\Event\BaseEvent;
use App\JeMengageTimelineFeed\JeMengageTimelineFeedEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TimelineEventNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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

    protected function getType(): string
    {
        return JeMengageTimelineFeedEnum::EVENT;
    }

    /** @param BaseEvent $object */
    protected function getDescription(object $object): ?string
    {
        return sprintf('%s • %s • %s',
            $object->getName(),
            $this->formatDate($object->getLocalBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
            $object->getInlineFormattedAddress()
        );
    }

    /** @param BaseEvent $object */
    protected function isLocal(object $object): bool
    {
        return true;
    }

    /** @param BaseEvent $object */
    protected function getImage(object $object): ?string
    {
        return $object->getImageName() ? $this->urlGenerator->generate(
            'asset_url',
            ['path' => $object->getImagePath()],
            UrlGeneratorInterface::ABSOLUTE_URL
        ) : null;
    }

    /** @param BaseEvent $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param BaseEvent $object */
    protected function getTimeZone(object $object): ?string
    {
        return $object->getTimeZone();
    }

    /** @param BaseEvent $object */
    protected function getAuthor(object $object): ?string
    {
        return $object->getAuthor() ? $object->getAuthor()->getFullName() : null;
    }

    /** @param BaseEvent $object */
    protected function getDeepLink(object $object): ?string
    {
        return null;
    }
}
