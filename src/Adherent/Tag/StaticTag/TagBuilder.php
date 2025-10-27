<?php

namespace App\Adherent\Tag\StaticTag;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagGenerator\EventTagGenerator;
use App\Entity\AdherentStaticLabel;
use App\Entity\NationalEvent\NationalEvent;
use App\Repository\AdherentStaticLabelRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Component\String\UnicodeString;

class TagBuilder
{
    public function __construct(
        private readonly NationalEventRepository $nationalEventRepository,
        private readonly AdherentStaticLabelRepository $staticLabelRepository,
    ) {
    }

    public function buildAll(): array
    {
        $events = $this->nationalEventRepository->findAllSince(new \DateTime(EventTagGenerator::PERIOD));

        return array_values(array_unique(array_merge(
            array_map([$this, 'buildForEvent'], array_filter($events, static fn (NationalEvent $event) => $event->endDate > new \DateTime())),
            array_map(fn (NationalEvent $event) => $this->buildForEvent($event, true), array_filter($events, static fn (NationalEvent $event) => $event->startDate < new \DateTime())),
            array_map(static fn (AdherentStaticLabel $label) => $label->getIdentifier(), $this->staticLabelRepository->findAllLikeAdherentTags()),
        )));
    }

    public function buildForEvent(NationalEvent $event, bool $isPresent = false): string
    {
        return TagEnum::getNationalEventTag($event->getSlug(), $event->startDate < new \DateTime() && $isPresent);
    }

    public function buildLabelFromSlug(string $slug): string
    {
        return $this->nationalEventRepository->findOneBySlug($slug)?->getName() ?? (new UnicodeString(str_replace('-', ' ', $slug)))->title(true);
    }
}
