<?php

namespace App\Adherent\Tag\StaticTag;

use App\Adherent\Tag\TagEnum;
use App\Entity\NationalEvent\NationalEvent;
use App\Repository\NationalEvent\NationalEventRepository;

class EventTagBuilder
{
    public function __construct(private readonly NationalEventRepository $repository)
    {
    }

    public function buildAll(): array
    {
        return array_unique(array_map([$this, 'buildForEvent'], $this->repository->findAll()));
    }

    public function buildForEvent(NationalEvent $event): string
    {
        return TagEnum::getNationalEventTag($event->startDate->format('Y') ?? date('Y'));
    }
}
