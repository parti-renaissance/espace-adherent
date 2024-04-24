<?php

namespace App\Adherent\Tag\StaticTag;

use App\Adherent\Tag\TagEnum;
use App\Entity\NationalEvent\NationalEvent;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Component\String\UnicodeString;

class EventTagBuilder
{
    public function __construct(private readonly NationalEventRepository $repository)
    {
    }

    public function buildAll(): array
    {
        return array_map([$this, 'buildForEvent'], $this->repository->findAll());
    }

    public function buildForEvent(NationalEvent $event): string
    {
        return TagEnum::getNationalEventTag($event->getSlug());
    }

    public function buildLabelFromSlug(string $slug)
    {
        return $this->repository->findOneBySlug($slug)?->name ?? (new UnicodeString(str_replace('-', ' ', $slug)))->title(true);
    }
}
