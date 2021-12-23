<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Event\EventTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event\DefaultEventRepository")
 *
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"},
 * )
 */
class DefaultEvent extends BaseEvent
{
    use DefaultCategoryOwnerTrait;

    public function getType(): string
    {
        return EventTypeEnum::TYPE_DEFAULT;
    }
}
