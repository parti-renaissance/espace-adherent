<?php

namespace App\Entity\Event;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Event\EventTypeEnum;
use App\Repository\Event\DefaultEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity(repositoryClass: DefaultEventRepository::class)]
class DefaultEvent extends BaseEvent
{
    public function getType(): string
    {
        return EventTypeEnum::TYPE_DEFAULT;
    }
}
