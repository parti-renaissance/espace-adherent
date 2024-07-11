<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Event\EventTypeEnum;
use App\Repository\Event\DefaultEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DefaultEventRepository::class)]
#[ApiResource(collectionOperations: [], itemOperations: ['get'])]
class DefaultEvent extends BaseEvent
{
    public function getType(): string
    {
        return EventTypeEnum::TYPE_DEFAULT;
    }
}
