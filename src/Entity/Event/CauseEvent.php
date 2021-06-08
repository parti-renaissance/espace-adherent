<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Coalition\Cause;
use App\Event\EventTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"event_read", "image_owner_exposed"}},
 *         "pagination_client_items_per_page": true,
 *         "order": {"beginAt": "DESC"},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CauseEventRepository")
 */
class CauseEvent extends BaseEvent
{
    use DefaultCategoryOwnerTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Cause", inversedBy="events")
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"event_write", "event_read", "event_list_read"})
     */
    private $cause;

    public function getType(): string
    {
        return EventTypeEnum::TYPE_CAUSE;
    }

    public function getCause(): ?Cause
    {
        return $this->cause;
    }

    public function setCause(Cause $cause): void
    {
        $this->cause = $cause;
    }

    public function isCoalitionsEvent(): bool
    {
        return true;
    }

    public function needNotifyForRegistration(): bool
    {
        return true;
    }

    public function needNotifyForCancellation(): bool
    {
        return true;
    }
}
